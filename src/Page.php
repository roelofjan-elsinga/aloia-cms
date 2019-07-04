<?php

namespace FlatFileCms;


use Carbon\Carbon;
use ContentParser\ContentParser;
use FlatFileCms\Contracts\PageInterface;
use FlatFileCms\Contracts\StorableInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Page extends Content implements PageInterface
{
    private $page;

    /**
     * Article constructor.
     * @param array $page
     */
    public function __construct(array $page)
    {
        $this->page = $page;
    }

    /**
     * Get an page by slug
     *
     * @param string $slug
     * @return null|Page
     */
    public static function forSlug(string $slug): ?Page
    {
        return self::all()
            ->filter(function (Page $page) use ($slug) {
                return $page->slug() === $slug;
            })
            ->first();
    }
    
    /**
     * Get the slug of this page
     *
     * @return string
     */
    public function slug(): string
    {
        return pathinfo($this->page['filename'])['filename'] ?? "";
    }

    /**
     * Get the type of this page
     *
     * @return string
     */
    public function type(): string
    {
        return "website";
    }

    /**
     * Get the filename of this page
     *
     * @return string
     */
    public function filename(): string
    {
        return $this->page['filename'];
    }

    /**
     * Get the main image of this page
     *
     * @return string
     * @throws \Exception
     */
    public function image(): string
    {
        return $this->page['image'];
    }

    /**
     * Get the path to the thumbnail of this page
     *
     * @return string
     * @throws \Exception
     */
    public function thumbnail(): string
    {
        return $this->page['image'];
    }

    /**
     * Get the formatted post date of this page
     *
     * @return string
     */
    public function postDate(): string
    {
        return $this->rawPostDate()->format("F jS, Y");
    }

    /**
     * Get a Carbon instance of the post date of this page
     *
     * @return Carbon
     */
    public function rawPostDate(): Carbon
    {
        return Carbon::createFromFormat("Y-m-d", $this->page['postDate'])
            ->setTimeFromTimeString("12:00:00");
    }

    /**
     * Get the formatted update date for this page
     *
     * @return string
     */
    public function updatedDate(): string
    {
        return $this->rawUpdatedDate()->format("F jS, Y");
    }

    /**
     * Get a Carbon instance of the update date of this page
     *
     * @return Carbon
     */
    public function rawUpdatedDate(): Carbon
    {
        try {
            return isset($this->page['updateDate'])
                ? Carbon::createFromFormat("Y-m-d H:i:s", $this->page['updateDate'])
                : $this->rawPostDate();
        } catch (\Exception $exception) {
            return $this->rawPostDate();
        }
    }

    /**
     * Determine whether this page is published
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->page['isPublished'] ?? false;
    }

    /**
     * Determine whether this page is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->page['isScheduled'] ?? false;
    }

    /**
     * Get the title of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function title(): string
    {
        return $this->page['title'] ?? $this->getParsedTitleFromContent();
    }

    /**
     * Get the parsed title from the page content
     *
     * @return string
     * @throws \Exception
     */
    private function getParsedTitleFromContent(): string
    {
        $file_content = $this->content();

        $titles = HtmlParser::getTextBetweenTags($file_content, 'h1');

        return $titles[0] ?? "Untitled page";
    }

    /**
     * Get the parsed body of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function content(): string
    {
        return ContentParser::forFile($this->getFilePath())->parse();
    }

    /**
     * Get the file path of this page
     *
     * @return string
     */
    private function getFilePath(): string
    {
        return $this->getFolderPath() . '/' . $this->filename();
    }

    /**
     * Get the path of the folder than contains all pages
     *
     * @return string
     */
    public function getFolderPath(): string
    {
        return Config::get('flatfilecms.pages.folder_path');
    }

    /**
     * Get the raw body of this resource
     *
     * @return string
     */
    public function rawContent(): string
    {
        return file_get_contents($this->getFilePath());
    }

    /**
     * Get the description of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function description(): string
    {
        return $this->page['description'] ?? $this->getDescriptionFromContent();
    }

    /**
     * Get the canonical if it's set
     *
     * @return null|string
     */
    public function canonicalLink(): ?string
    {
        return $this->page['canonical'] ?? null;
    }

    /**
     * Get the author of this page
     *
     * @return string
     */
    public function author(): string
    {
        return $this->page['author'] ?? '';
    }

    /**
     * Get the summary of this page
     *
     * @return string
     */
    public function summary(): string
    {
        return $this->page['summary'] ?? '';
    }

    /**
     * Get the template name of this page
     *
     * @return string
     */
    public function templateName(): string
    {
        return $this->page['template_name'] ?? 'default';
    }

    /**
     * Determine whether this page is in the menu
     *
     * @return bool
     */
    public function isInMenu(): bool
    {
        return $this->page['in_menu'] ?? false;
    }

    /**
     * Get the keywords of this page
     *
     * @return string
     */
    public function keywords(): string
    {
        return $this->page['keywords'] ?? '';
    }

    /**
     * Get the path to the meta data file
     *
     * @return string
     */
    protected static function getMetaDataFilePath(): string
    {
        return Config::get('flatfilecms.pages.file_path');
    }

    /**
     * Get all content resources
     *
     * @return Collection|StorableInterface[]
     */
    public static function all(): Collection
    {
        $file_path = self::getMetaDataFilePath();

        self::validateMetaDataFile($file_path);

        return Collection::make(json_decode(File::get($file_path), true))
            ->map(function ($page) {
                return new static($page);
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
        if(! File::exists($file_path)) {
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
                ->map(function($article) {
                    return \FlatFileCms\DataSource\Page::create($article)->toArray();
                })
                ->toJson(JSON_PRETTY_PRINT)
        );
    }
}