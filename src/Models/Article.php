<?php


namespace FlatFileCms\Models;

use FlatFileCms\HtmlParser;
use FlatFileCms\Models\Contracts\PublishInterface;

class Article extends Model implements ModelInterface, PublishInterface
{
    protected $folder = 'articles';

    /**
     * Determine whether this article is published
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->matter['is_published'] ?? false;
    }
}
