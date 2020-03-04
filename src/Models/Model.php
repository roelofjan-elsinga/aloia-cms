<?php


namespace AloiaCms\Models;

use ContentParser\ContentParser;
use AloiaCms\InlineBlockParser;
use AloiaCms\Models\Contracts\ModelInterface;
use AloiaCms\Models\Contracts\StorableInterface;
use AloiaCms\Writer\FolderCreator;
use AloiaCms\Writer\FrontMatterCreator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Model implements ModelInterface, StorableInterface
{
    protected $folder = '';

    protected $file_name = null;

    protected $extension = 'md';

    protected $matter = [];

    protected $body = '';

    protected $required_fields = [];

    /**
     * Find a single model
     *
     * @param string $file_name
     * @return ModelInterface
     */
    public static function find(string $file_name): ModelInterface
    {
        $instance = new static();

        $instance->setFileName($file_name);

        return $instance;
    }

    /**
     * Return all instances of the model
     *
     * @return Collection|ModelInterface[]
     */
    public static function all(): Collection
    {
        $instance = new static();

        $files = File::allFiles($instance->getFolderPath());

        return Collection::make($files)
            ->map(function (\SplFileInfo $fileInfo): ModelInterface {
                return self::find(pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME));
            });
    }

    /**
     * Rename this file to the given name
     *
     * @param string $new_name
     * @return Model
     */
    public function rename(string $new_name): ModelInterface
    {
        $old_file_path = $this->getFilePath();

        $this->file_name = $new_name;

        $new_file_path = $this->getFilePath();

        File::move($old_file_path, $new_file_path);

        return self::find($new_name);
    }

    /**
     * Get the raw content of the file + front matter
     *
     * @return string
     */
    public function rawContent(): string
    {
        $file_path = $this->getFilePath();

        if ($this->exists()) {
            return file_get_contents($file_path);
        }

        return "";
    }

    /**
     * Parse the file for this model into model variables
     */
    private function parseFile(): void
    {
        $parsed_file = YamlFrontMatter::parse($this->rawContent());

        $this->matter = $parsed_file->matter();
        $this->body = $parsed_file->body();
    }

    /**
     * Save this instance to file
     *
     * @return ModelInterface
     * @throws \Exception
     */
    public function save(): ModelInterface
    {
        $file_content = FrontMatterCreator::seed($this->matter, $this->body)->create();

        $this->assertFilenameExists();

        $this->assertRequiredMatterIsPresent();

        $file_path = $this->getFilePath();

        file_put_contents($file_path, $file_content);

        return $this;
    }

    /**
     * Get the file path for this instance
     *
     * @return string
     */
    private function getFilePath(): string
    {
        $folder_path = $this->getFolderPath();

        if (!is_null($matching_filepath = $this->getFileMatchFromDisk())) {
            $this->setExtension(pathinfo($matching_filepath, PATHINFO_EXTENSION));
        }

        return "{$folder_path}/{$this->file_name}.{$this->extension}";
    }

    /**
     * Get the folder path for this model
     *
     * @return string
     */
    public function getFolderPath(): string
    {
        $folder_path = Config::get('aloiacms.collections_path') . "/{$this->folder}";

        FolderCreator::forPath($folder_path);

        return $folder_path;
    }

    /**
     * Get the front matter
     *
     * @return array
     */
    public function matter(): array
    {
        return $this->matter;
    }

    /**
     * Add data to the front matter
     *
     * @param string $key
     * @param mixed $value
     * @return ModelInterface
     */
    public function addMatter(string $key, $value): ModelInterface
    {
        $this->matter[$key] = $value;

        return $this;
    }

    /**
     * Set the front matter
     *
     * @param array $matter
     * @return ModelInterface
     */
    public function setMatter(array $matter): ModelInterface
    {
        $this->matter = $matter;

        return $this;
    }

    /**
     * Get the raw file body
     *
     * @return string
     */
    public function rawBody(): string
    {
        return $this->body;
    }

    /**
     * Get the parse file body
     *
     * @return string
     */
    public function body(): string
    {
        $content = new ContentParser($this->rawBody(), $this->extension());

        return (new InlineBlockParser)->parseHtmlString($content->parse());
    }

    /**
     * Set the file body
     *
     * @param string $body
     * @return ModelInterface
     */
    public function setBody(string $body): ModelInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * Set the file extension
     *
     * @param string $extension
     * @return ModelInterface
     */
    public function setExtension(string $extension): ModelInterface
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get all models for this type
     *
     * @return array
     */
    private function getModelFiles(): array
    {
        return File::allFiles($this->getFolderPath());
    }

    /**
     * Get the filename from disk
     *
     * @return string|null
     */
    private function getFileMatchFromDisk(): ?string
    {
        return Arr::first($this->getModelFiles(), function (string $file_name) {
            return strpos($file_name, "/{$this->file_name}.");
        });
    }

    /**
     * Determine whether the current model exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return !is_null($this->getFileMatchFromDisk());
    }

    /**
     * Delete the current model
     *
     * @return bool
     */
    public function delete(): bool
    {
        return File::delete($this->getFilePath());
    }

    /**
     * Set the file name for this instance
     *
     * @param string $file_name
     * @return ModelInterface
     */
    protected function setFileName(string $file_name): ModelInterface
    {
        $this->file_name = $file_name;

        $this->parseFile();

        return $this;
    }

    /**
     * Get front matter information through an accessor
     *
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->matter[$key] ?? null;
    }

    /**
     * Throw exception when the file name is not set for this instance
     *
     * @throws \Exception
     */
    private function assertFilenameExists()
    {
        if (is_null($this->file_name)) {
            throw new \Exception("Filename is required");
        }
    }

    /**
     * Throw exception if at least one required matter attribute is not present
     *
     * @throws \Exception
     */
    private function assertRequiredMatterIsPresent()
    {
        foreach ($this->required_fields as $required_field) {
            if (!isset($this->matter[$required_field])) {
                throw new \Exception("Attribute {$required_field} is required");
            }
        }
    }
}
