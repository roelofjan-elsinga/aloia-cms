<?php

namespace FlatFileCms\DataSource;

use FlatFileCms\Contracts\DataSourceInterface;

class Page implements DataSourceInterface
{
    private $attributes;

    private function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Create a new instance of the data source
     *
     * @param array $attributes
     * @return mixed
     */
    public static function create(array $attributes): DataSourceInterface
    {
        return new static($attributes);
    }

    /**
     * Convert the data source to an array
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $optional_fields = [
            'summary',
            'updateDate',
            'thumbnail',
            'canonical',
            'author',
            'in_menu',
            'is_homepage',
            'keywords',
            'image',
            'category',
            'menu_name',
            'meta_data',
        ];
        
        $resource_fields = [
            'title' => $this->getRequiredField('title'),
            'filename' => $this->getRequiredField('filename'),
            'description' => $this->getRequiredField('description'),
            'postDate' => $this->getRequiredField('postDate'),
            'isPublished' => $this->getRequiredField('isPublished'),
            'isScheduled' => $this->getRequiredField('isScheduled'),
            'summary' => $this->getRequiredField('summary'),
            'template_name' => $this->getRequiredField('template_name'),
        ];
        
        foreach ($optional_fields as $field_name) {
            if ($this->has($field_name)) {
                $resource_fields[$field_name] = $this->attributes[$field_name];
            }
        }

        return $resource_fields;
    }

    private function has(string $string)
    {
        return isset($this->attributes[$string])
            && !is_null($this->attributes[$string]);
    }

    /**
     * @param string $field_name
     * @return mixed
     * @throws \Exception
     */
    private function getRequiredField(string $field_name)
    {
        if (!$this->has($field_name)) {
            throw new \Exception("Attribute {$field_name} is required");
        }

        return $this->attributes[$field_name];
    }
}
