<?php


namespace FlatFileCms\DataSource;


use FlatFileCms\Contracts\DataSourceInterface;

class Taxonomy implements DataSourceInterface
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
        return [
            "category_url_prefix" => $this->attributes['category_url_prefix'],
            "category_name" => $this->attributes['category_name'],
            "children" => $this->attributes['children']
        ];
    }
}