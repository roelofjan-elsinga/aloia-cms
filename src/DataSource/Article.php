<?php

namespace FlatFileCms\DataSource;

use FlatFileCms\Contracts\DataSourceInterface;

/**
 * @deprecated deprecated since version 1.0.0
 */
class Article implements DataSourceInterface
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
            'updateDate',
            'thumbnail',
            'description',
            'isScheduled',
            'isPublished',
            'url',
            'canonical',
        ];

        $resource_fields = [
            'filename' => $this->getRequiredField('filename'),
            'postDate' => $this->getRequiredField('postDate')
        ];

        foreach ($optional_fields as $field_name) {
            if ($this->has($field_name)) {
                $resource_fields[$field_name] = $this->attributes[$field_name];
            }
        }

        return $resource_fields;
    }

    /**
     * Determine whether the given attribute name exists
     *
     * @param string $string
     * @return bool
     */
    private function has(string $string): bool
    {
        return isset($this->attributes[$string]);
    }

    /**
     * Get the required field value and through an exception if it doesn't exist
     *
     * @param string $field_name
     * @return string
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
