<?php


namespace FlatFileCms\DataSource;


class File
{
    /**
     * @var string
     */
    private $file_path;

    public function __construct(string $file_path)
    {
        $this->file_path = $file_path;
    }

    /**
     * Named constructor
     *
     * @param string $file_path
     * @return File
     */
    public static function forFilePath(string $file_path): File
    {
        return new static($file_path);
    }

    /**
     * @param string $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, $arguments)
    {
        return \Illuminate\Support\Facades\File::{$name}($this->file_path);
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->file_path;
    }
}