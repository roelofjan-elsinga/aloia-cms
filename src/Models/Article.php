<?php


namespace AloiaCms\Models;

use AloiaCms\HtmlParser;
use AloiaCms\Models\Contracts\ModelInterface;
use AloiaCms\Models\Contracts\PublishInterface;
use AloiaCms\Models\Traits\Postable;
use AloiaCms\Models\Traits\Publishable;
use AloiaCms\Models\Traits\Updatable;
use Illuminate\Support\Facades\Config;

class Article extends Model implements ModelInterface, PublishInterface
{
    use Publishable, Postable, Updatable;

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

        $titles = [];

        if (!empty($file_content)) {
            $titles = HtmlParser::getTextBetweenTags($file_content, 'h1');
        }

        return $titles[0] ?? "Untitled article";
    }

    /**
     * Get the slug of this article
     *
     * @return string
     */
    public function slug(): string
    {
        return $this->file_name;
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

        $images = [];

        if (!empty($file_content)) {
            $images = HtmlParser::getTagAttribute($file_content, 'img', 'src');
        }

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
        return $this->matter['thumbnail'] ?? "";
    }

    /**
     * Get the description of this article
     *
     * @return string
     * @throws \Exception
     */
    public function description(): string
    {
        return $this->matter['description'] ?? $this->getDescriptionFromContent();
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

    /**
     * Get the external URL if it's set
     *
     * @return null|string
     */
    public function externalUrl(): ?string
    {
        return $this->matter['external_url'] ?? null;
    }
}
