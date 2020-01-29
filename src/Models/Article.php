<?php


namespace FlatFileCms\Models;

use FlatFileCms\HtmlParser;
use FlatFileCms\Models\Contracts\ModelInterface;
use FlatFileCms\Models\Contracts\PublishInterface;
use FlatFileCms\Models\Traits\Postable;
use FlatFileCms\Models\Traits\Publishable;
use FlatFileCms\Models\Traits\Updatable;
use Illuminate\Support\Facades\Config;

class Article extends Model implements ModelInterface, PublishInterface
{
    use Publishable, Postable, Updatable;

    protected $folder = 'articles';

    protected $required_fields = [
        'post_date'
    ];

    /**
     * Get the title of this article
     *
     * @return string
     * @throws \Exception
     */
    public function title(): string
    {
        if (isset($this->matter['title'])) {
            return $this->matter['title'];
        }

        $file_content = $this->body();

        $titles = HtmlParser::getTextBetweenTags($file_content, 'h1');

        return $titles[0] ?? "Untitled article";
    }

    /**
     * Get the main image of this article
     *
     * @return string
     * @throws \Exception
     */
    public function image(): string
    {
        if (isset($this->matter['image'])) {
            return $this->matter['image'];
        }

        $file_content = $this->body();

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
        if (isset($this->matter['thumbnail'])) {
            return $this->matter['thumbnail'];
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
     * Generate a description for the content
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function getDescriptionFromContent(): string
    {
        $paragraphs = HtmlParser::getTextBetweenTags($this->body(), 'p');

        $paragraphs_with_text_content = array_filter($paragraphs, function ($paragraph) {
            return !empty(strip_tags($paragraph));
        });

        if (count($paragraphs_with_text_content) > 0) {
            return substr(head($paragraphs_with_text_content), 0, 160);
        }

        return "";
    }

    /**
     * Get the canonical if it's set
     *
     * @return null|string
     */
    public function canonicalLink(): ?string
    {
        return $this->matter['canonical'] ?? null;
    }
}
