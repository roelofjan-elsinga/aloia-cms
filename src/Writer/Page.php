<?php


namespace FlatFileCms\Writer;

use Illuminate\Support\Facades\Config;

class Page
{
    public $folder = 'pages';

    public $filename = null;

    private function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public static function open(string $filename): Page
    {
        return new static($filename);
    }

    public function read(): string
    {
        $file_path = $this->getFilePath();
        return file_get_contents($file_path);
    }

    public function write(string $content): void
    {
        $file_path = $this->getFilePath();
        file_put_contents($file_path, $content);
    }

    private function getFilePath(): string
    {
        $folder_path = Config::get('flatfilecms.collections_path') . "/{$this->folder}";

        $this->createFolderIfNotExists($folder_path);

        return "{$folder_path}/{$this->filename}";
    }

    private function createFolderIfNotExists(string $folder_path)
    {
        if (!file_exists($folder_path)) {
            mkdir($folder_path);
        }
    }
}
