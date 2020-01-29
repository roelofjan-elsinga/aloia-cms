<?php

namespace FlatFileCms;

use Carbon\Carbon;
use ContentParser\ContentParser;
use FlatFileCms\Contracts\ArticleInterface;
use FlatFileCms\Models\Contracts\StorableInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Article extends Content implements ArticleInterface, StorableInterface
{
    private $article;

    /**
     * Article constructor.
     * @param array $article
     */
    public function __construct(array $article)
    {
        $this->article = $article;
    }

    /**
     * Get an article by slug
     *
     * @param string $slug
     * @return null|Article
     */
    public static function forSlug(string $slug): ?Article
    {
        return self::all()
            ->filter(function (Article $article) use ($slug) {
                return $article->slug() === $slug;
            })
            ->first();
    }

    /**
     * Return a new instance of this Article
     *
     * @return Article|null
     */
    public static function instance(): ?Article
    {
        return new static([]);
    }

    /**
     * Get the slug of this article
     *
     * @param bool $including_parents
     * @return string
     */
    public function slug(bool $including_parents = false): string
    {
        $slug_prefix = '';

        if ($including_parents) {
            $slug_prefix = Config::get('flatfilecms.articles.url_prefix');
            $slug_prefix .= '/';
        }

        return $slug_prefix . pathinfo($this->article['filename'], PATHINFO_FILENAME) ?? "";
    }

    /**
     * Get the type of this article
     *
     * @return string
     */
    public function type(): string
    {
        return "article";
    }

    /**
     * Get the filename of this article
     *
     * @return string
     */
    public function filename(): string
    {
        return $this->article['filename'] ?? "";
    }

    /**
     * Get the filename of this article
     *
     * @return string
     */
    public function fileType(): string
    {
        return pathinfo($this->article['filename'], PATHINFO_EXTENSION) ?? "";
    }

    /**
     * Get the main image of this article
     *
     * @return string
     * @throws \Exception
     */
    public function image(): string
    {
        $file_content = $this->content();

        $images = HtmlParser::getTagAttribute($file_content, 'img', 'src');

        return $images[0] ?? "";
    }

    /**
     * Get the path to the thumbnail of this article
     *
     * @return string
     * @throws \Exception
     */
    public function thumbnail(): string
    {
        if (isset($this->article['thumbnail'])) {
            return $this->article['thumbnail'];
        }

        if (!empty($this->image())) {
            return "/{$this->getImagesUrlPath()}/{$this->getThumbnailFromPath($this->image())}";
        }

        return "";
    }

    /**
     * @param string $path
     * @param int $width
     * @return string
     */
    private function getThumbnailFromPath(string $path, int $width = 300): string
    {
        $basename = basename($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $filename = str_replace(".{$extension}", "", $basename);

        return "{$filename}_w{$width}.{$extension}";
    }

    /**
     * Get the path of the folder than contains all articles
     *
     * @return string
     */
    private function getImagesUrlPath(): string
    {
        return Config::get('flatfilecms.articles.image_path');
    }

    /**
     * Get the formatted post date of this article
     *
     * @return string
     */
    public function postDate(): string
    {
        return $this->rawPostDate()->format("F jS, Y");
    }

    /**
     * Get a Carbon instance of the post date of this article
     *
     * @return Carbon
     */
    public function rawPostDate(): Carbon
    {
        return Carbon::createFromFormat("Y-m-d", $this->article['postDate'])
            ->setTimeFromTimeString("12:00:00");
    }

    /**
     * Get the formatted update date for this article
     *
     * @return string
     */
    public function updatedDate(): string
    {
        return $this->rawUpdatedDate()->format("F jS, Y");
    }

    /**
     * Get a Carbon instance of the update date of this article
     *
     * @return Carbon
     */
    public function rawUpdatedDate(): Carbon
    {
        try {
            return isset($this->article['updateDate'])
                ? Carbon::createFromFormat("Y-m-d H:i:s", $this->article['updateDate'])
                : $this->rawPostDate();
        } catch (\Exception $exception) {
            return $this->rawPostDate();
        }
    }

    /**
     * Get the title of this article
     *
     * @return string
     * @throws \Exception
     */
    public function title(): string
    {
        $file_content = $this->content();

        $titles = HtmlParser::getTextBetweenTags($file_content, 'h1');

        return $titles[0] ?? "Untitled article";
    }

    /**
     * Get the parsed body of this article
     *
     * @return string
     * @throws \Exception
     */
    public function content(): string
    {
        return $this->parseContentBlocks(
            ContentParser::forFile($this->getFilePath())->parse()
        );
    }

    /**
     * Get the raw body of this article
     *
     * @return string
     */
    public function rawContent(): string
    {
        return file_get_contents($this->getFilePath());
    }

    /**
     * Get the description of this article
     *
     * @return string
     * @throws \Exception
     */
    public function description(): string
    {
        return $this->article['description'] ?? $this->getDescriptionFromContent();
    }

    /**
     * Get the file path of this article
     *
     * @return string
     */
    private function getFilePath(): string
    {
        return $this->getFolderPath() . '/' . $this->filename();
    }

    /**
     * Get the path of the folder than contains all articles
     *
     * @return string
     */
    public function getFolderPath(): string
    {
        return Config::get('flatfilecms.articles.folder_path');
    }

    /**
     * Get the canonical if it's set
     *
     * @return null|string
     */
    public function canonicalLink(): ?string
    {
        return $this->article['canonical'] ?? null;
    }

    /**
     * Get the URL if it's set
     *
     * @return null|string
     */
    public function url(): ?string
    {
        return $this->article['url'] ?? null;
    }

    /**
     * Determine whether this article is published
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->article['isPublished'] ?? false;
    }

    /**
     * Determine whether this article is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->article['isScheduled'] ?? false;
    }

    /**
     * Get the path to the meta data file
     *
     * @return string
     */
    protected static function getMetaDataFilePath(): string
    {
        return Config::get('flatfilecms.articles.file_path');
    }

    /**
     * Get all articles
     *
     * @return Collection|Article[]
     */
    public static function all(): Collection
    {
        $file_path = self::getMetaDataFilePath();

        self::validateMetaDataFile($file_path);

        return Collection::make(json_decode(File::get($file_path), true))
            ->map(function ($article) {
                return new static($article);
            });
    }

    /**
     * Get the raw meta data without converting the data to an Article
     *
     * @return Collection
     */
    public static function raw(): Collection
    {
        $file_path = self::getMetaDataFilePath();

        self::validateMetaDataFile($file_path);

        return Collection::make(json_decode(File::get($file_path), true));
    }

    /**
     * Check if the meta data file exists and create it if it doesn't
     *
     * @param string $file_path
     * @return void
     */
    protected static function validateMetaDataFile(string $file_path)
    {
        $articles_folder_path = Config::get('flatfilecms.articles.folder_path');

        if (! is_dir($articles_folder_path)) {
            mkdir($articles_folder_path);
        }

        if (! file_exists($file_path)) {
            self::update(
                new Collection()
            );
        }
    }

    /**
     * Update the meta data file with updated articles meta data
     *
     * @param Collection $articles
     */
    public static function update(Collection $articles): void
    {
        $file_path = self::getMetaDataFilePath();

        File::put(
            $file_path,
            $articles
                ->map(function ($article) {
                    return \FlatFileCms\DataSource\Article::create($article)->toArray();
                })
                ->toJson(JSON_PRETTY_PRINT)
        );
    }

    /**
     * Delete an article by slug
     *
     * @param string $slug
     */
    public static function deleteBySlug(string $slug): void
    {
        $file_path = self::getMetaDataFilePath();

        $filename = null;

        File::put(
            $file_path,
            self::raw()
                ->filter(function (array $article) use (&$filename, $slug) {
                    $matches = strpos($article['filename'], $slug) !== false;

                    if ($matches) {
                        $filename = $article['filename'];
                    }

                    return !$matches;
                })
                ->map(function ($article) {
                    return \FlatFileCms\DataSource\Article::create($article)->toArray();
                })
                ->toJson(JSON_PRETTY_PRINT)
        );

        if ($filename !== null) {
            File::delete(Config::get('flatfilecms.articles.folder_path') . '/' . $filename);
        }
    }

    /**
     * Get all articles
     *
     * @return Collection
     */
    public static function published(): Collection
    {
        return Article::all()
            ->filter(function (Article $article) {
                return $article->isPublished();
            })
            ->values();
    }

    /**
     * Call the class method if it exists, otherwise return null
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        return null;
    }
}
