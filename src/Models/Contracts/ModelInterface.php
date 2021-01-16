<?php


namespace AloiaCms\Models\Contracts;

use Illuminate\Support\Collection;

interface ModelInterface
{
    /**
     * Find the model by the given value
     *
     * @param string $file_name
     * @return ModelInterface
     */
    public static function find(string $file_name): ModelInterface;

    /**
     * Get all instances of the model
     *
     * @return Collection
     */
    public static function all(): Collection;

    /**
     * Get athe front matter of this model
     *
     * @return array
     */
    public function matter(): array;

    /**
     * Add front matter by key/value pairs
     *
     * @param string $key
     * @param $value
     * @return ModelInterface
     */
    public function addMatter(string $key, $value): ModelInterface;

    /**
     * Set and overwrite the front matter on this model
     *
     * @param array $matter
     * @return ModelInterface
     */
    public function setMatter(array $matter): ModelInterface;

    /**
     * Get the parsed body of this model
     *
     * @return string
     */
    public function body(): string;

    /**
     * Set the body for this model
     *
     * @param string $body
     * @return ModelInterface
     */
    public function setBody(string $body): ModelInterface;

    /**
     * Get the file extension of this model
     *
     * @return string
     */
    public function extension(): string;

    /**
     * Set the file extension for this model
     *
     * @param string $extension
     * @return ModelInterface
     */
    public function setExtension(string $extension): ModelInterface;

    /**
     * Get the filename of this model
     *
     * @return string|null
     */
    public function filename(): ?string;

    /**
     * Get the absolute path of the folder in which this resource is saved
     *
     * @return string
     */
    public function getFolderPath(): string;

    /**
     * Save this model to the file system
     *
     * @return ModelInterface
     */
    public function save(): ModelInterface;

    /**
     * Determine whether this model exists
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Delete this model
     *
     * @return bool
     */
    public function delete(): bool;
}
