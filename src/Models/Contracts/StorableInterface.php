<?php

namespace FlatFileCms\Models\Contracts;

interface StorableInterface
{
    /**
     * Get the absolute path of the folder in which this resource is saved
     *
     * @return string
     */
    public function getFolderPath(): string;
}
