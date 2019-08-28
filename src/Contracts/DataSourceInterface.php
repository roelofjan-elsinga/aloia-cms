<?php

namespace FlatFileCms\Contracts;

interface DataSourceInterface
{

    /**
     * Create a new instance of the data source
     *
     * @param array $attributes
     * @return mixed
     */
    public static function create(array $attributes): DataSourceInterface;

    /**
     * Convert the data source to an array
     *
     * @return array
     */
    public function toArray(): array;
}
