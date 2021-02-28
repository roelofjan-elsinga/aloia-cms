<?php


namespace AloiaCms\Models;

use AloiaCms\Events\PostModelDeleted;
use AloiaCms\Events\PostModelSaved;
use AloiaCms\Events\PreModelDeleted;
use AloiaCms\Events\PreModelSaved;
use ContentParser\ContentParser;
use AloiaCms\InlineBlockParser;
use AloiaCms\Models\Contracts\ModelInterface;
use AloiaCms\Models\Contracts\StorableInterface;
use AloiaCms\Writer\FolderCreator;
use AloiaCms\Writer\FrontMatterCreator;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Model implements ModelInterface, StorableInterface
{
    /**
     * Represents the folder name where this model saves files
     *
     * @var string $folder
     */
    protected $folder = '';

    /**
     * Represents the basename of the base file
     *
     * @var string|null $file_name
     */
    protected $file_name = null;

    /**
     * Represents the filename of the base file
     *
     * @var string|null $full_file_name
     */
    protected $full_file_name = null;

    protected $extension = 'md';

    protected $matter = [];

    protected $body = '';

    protected $required_fields = [];

    /**
     * Return all instances of the model
     *
     * @return Collection|ModelInterface[]
     */
    public static function all(): Collection
    {
        return Collection::make((new static())->getModelFiles())
            ->map(fn (string $filename) => self::find(pathinfo($filename, PATHINFO_FILENAME)));
    }

    /**
     * Return the amount of models of this type
     *
     * @return int
     */
    public static function count(): int
    {
        return count((new static())->getModelFiles());
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
     * Parse the file for this model into model variables
     */
    private function parseFile(): void
    {
        $parsed_file = YamlFrontMatter::parse($this->rawContent());

        $this->matter = $parsed_file->matter();
        $this->body = $parsed_file->body();
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
     * Get the file path for this instance
     *
     * @return string
     */
    private function getFilePath(): string
    {
        $folder_path = $this->getFolderPath();

        if (!is_null($matching_filename = $this->getFullFileName())) {
            $this->setExtension(pathinfo($matching_filename, PATHINFO_EXTENSION));
        }

        return "{$folder_path}/{$this->file_name}.{$this->extension}";
    }

    /**
     * Get full file name (including extension) for this model.
     *
     * @return string|null
     */
    private function getFullFileName(): ?string
    {
        if (!$this->full_file_name) {
            $this->full_file_name = $this->getFileMatchFromDisk();
        }

        return $this->full_file_name;
    }

    /**
     * Get the filename from disk
     * This uses the least amount of loops possible.
     *
     * @return string|null
     */
    private function getFileMatchFromDisk(): ?string
    {
        $haystack = $this->getModelFiles();

        $min = 0;
        $max = count($haystack);

        // No saved files, lookup is pointless
        if ($max === 0) {
            return null;
        }

        while ($max >= $min) {
            $mid = floor(($min + $max) / 2);

            // Current key doesn't exist, so let's try a lower number
            if (!isset($haystack[$mid])) {
                $max = $mid - 1;
                continue;
            }

            if (strpos($haystack[$mid], "{$this->file_name}.") !== false) {
                return $haystack[$mid];
            } elseif ($haystack[$mid] < $this->file_name) {
                // The new chunk will be the second half
                $min = $mid + 1;
            } else {
                // The new chunk will be the first half
                $max = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Get all models for this type
     *
     * @return array
     */
    private function getModelFiles(): array
    {
        $filenames = array_values(
            array_diff(
                scandir($this->getFolderPath()),
                ['..', '.']
            )
        );

        sort($filenames);

        return $filenames;
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
     * Determine whether the current model exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->getFilePath());
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
     * Save this instance to file
     *
     * @return ModelInterface
     * @throws Exception
     */
    public function save(): ModelInterface
    {
        PreModelSaved::dispatch($this);

        $file_content = FrontMatterCreator::seed($this->matter, $this->body)->create();

        $this->assertFilenameExists();

        $this->assertRequiredMatterIsPresent();

        $file_path = $this->getFilePath();

        file_put_contents($file_path, $file_content);

        PostModelSaved::dispatch($this);

        return $this;
    }

    /**
     * Throw exception when the file name is not set for this instance
     *
     * @throws Exception
     */
    private function assertFilenameExists()
    {
        if (is_null($this->file_name)) {
            throw new Exception("Filename is required");
        }
    }

    /**
     * Throw exception if at least one required matter attribute is not present
     *
     * @throws Exception
     */
    private function assertRequiredMatterIsPresent()
    {
        foreach ($this->required_fields as $required_field) {
            if (!isset($this->matter[$required_field])) {
                throw new Exception("Attribute {$required_field} is required");
            }
        }
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
     * Set a value on the specified key in the configuration
     *
     * Kept around for backward compatibility
     *
     * @param string $key
     * @param $value
     * @return ModelInterface
     *
     * @deprecated since 3.2.0
     */
    public function addMatter(string $key, $value): ModelInterface
    {
        return $this->set($key, $value);
    }

    /**
     * Set a value on the specified key in the configuration
     *
     * @param string $key
     * @param $value
     * @return $this|ModelInterface
     */
    public function set(string $key, $value): ModelInterface
    {
        $this->matter[$key] = $value;

        return $this;
    }

    /**
     * Set data in the front matter, but only for the keys specified in the input array
     *
     * @param array $matter
     * @return ModelInterface
     */
    public function setMatter(array $matter): ModelInterface
    {
        foreach (array_keys($matter) as $key) {
            $this->matter[$key] = $matter[$key];
        }

        return $this;
    }

    /**
     * Remove a key from the configuration
     *
     * @param string $key
     * @return $this|ModelInterface
     */
    public function remove(string $key): ModelInterface
    {
        if ($this->has($key)) {
            unset($this->matter[$key]);
        }

        return $this;
    }

    /**
     * Determine whether a key is present in the configuration
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->matter[$key]);
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
     * Get the raw file body
     *
     * @return string
     */
    public function rawBody(): string
    {
        return $this->body;
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
     * Get the file name for this instance
     *
     * @return string
     */
    public function filename(): ?string
    {
        return $this->file_name;
    }

    /**
     * Delete the current model
     *
     * @return bool
     */
    public function delete(): bool
    {
        PreModelDeleted::dispatch($this);

        $is_successful = File::delete($this->getFilePath());

        PostModelDeleted::dispatch($this);

        return $is_successful;
    }

    /**
     * Get front matter information through an accessor
     *
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Get the value of the specified key, return null if it doesn't exist
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->matter[$key] ?? null;
    }
}
