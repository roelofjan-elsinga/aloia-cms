<?php

namespace FlatFileCms\DataSource;


use FlatFileCms\Contracts\DataSourceInterface;

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
     */
    public function toArray(): array
    {
        $data_source = [
            "filename" => $this->filename(),
            "description" => $this->description(),
            "postDate" => $this->postDate(),
            "isPublished" => $this->isPublished(),
            "isScheduled" => $this->isScheduled()
        ];

        if($this->has('updateDate')) {
            $data_source['updateDate'] = $this->updateDate();
        }

        if($this->has('thumbnail')) {
            $data_source['thumbnail'] = $this->thumbnail();
        }

        return $data_source;
    }

    private function filename()
    {
        return $this->attributes['filename'];
    }

    private function description()
    {
        return $this->attributes['description'];
    }

    private function postDate()
    {
        return $this->attributes['postDate'];
    }

    private function isPublished()
    {
        return $this->attributes['isPublished'];
    }

    private function isScheduled()
    {
        return $this->attributes['isScheduled'];
    }

    private function updateDate()
    {
        return $this->attributes['updateDate'];
    }

    private function thumbnail()
    {
        return $this->attributes['thumbnail'];
    }

    private function has(string $string)
    {
        return isset($this->attributes[$string]);
    }
}