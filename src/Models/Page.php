<?php

namespace FlatFileCms\Models;

use FlatFileCms\Contracts\PublishInterface;

class Page extends Model implements ModelInterface, PublishInterface
{
    protected $folder = 'pages';

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
