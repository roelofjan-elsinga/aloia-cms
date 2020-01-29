<?php


namespace FlatFileCms\Models;

use FlatFileCms\Contracts\StorableInterface;
use FlatFileCms\Writer\FolderCreator;
use FlatFileCms\Writer\FrontMatterCreator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Model implements ModelInterface, StorableInterface
{
    protected $folder = '';

    protected $file_name = null;

    protected $extension = 'md';

    protected $matter = [];

    protected $body = '';

    private function __construct(string $file_name)
    {
        $this->file_name = $file_name;

        $this->parseFile();
    }

    public static function find(string $file_name): ModelInterface
    {
        return new static($file_name);
    }

    public function rawContent(): string
    {
        $file_path = $this->getFilePath();

        if ($this->exists()) {
            return file_get_contents($file_path);
        }

        return "";
    }

    private function parseFile(): void
    {
        $parsed_file = YamlFrontMatter::parse($this->rawContent());

        $this->setMatter($parsed_file->matter());
        $this->setBody($parsed_file->body());
    }

    public function save(): ModelInterface
    {
        $file_content = FrontMatterCreator::seed($this->matter, $this->body)->create();

        $file_path = $this->getFilePath();

        file_put_contents($file_path, $file_content);

        return $this;
    }

    private function getFilePath(): string
    {
        $folder_path = $this->getFolderPath();

        FolderCreator::forPath($folder_path);

        return "{$folder_path}/{$this->file_name}.{$this->extension}";
    }

    public function getFolderPath(): string
    {
        return Config::get('flatfilecms.collections_path') . "/{$this->folder}";
    }

    public function matter(): array
    {
        return $this->matter;
    }

    public function addMatter(string $key, string $value): ModelInterface
    {
        $this->matter[$key] = $value;

        return $this;
    }

    public function setMatter(array $matter): ModelInterface
    {
        $this->matter = $matter;

        return $this;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function setBody(string $body): ModelInterface
    {
        $this->body = $body;

        return $this;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): ModelInterface
    {
        $this->extension = $extension;

        return $this;
    }

    public function exists(): bool
    {
        $files = glob($this->getFolderPath() . '/');

        $match = Arr::first($files, function (string $file_name) {
            return strpos($file_name, "/$this->file_name.");
        });

        return !is_null($match);
    }

    public static function fileExists(string $file_name): bool
    {
        $instance = new static($file_name);
        
        return $instance->exists();
    }
}
