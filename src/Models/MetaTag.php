<?php

namespace FlatFileCms\Models;

use FlatFileCms\Models\Contracts\ModelInterface;

class MetaTag extends Model implements ModelInterface
{
    protected $folder = 'meta_tags';

    protected $required_fields = [
        'title',
        'description',
        'author',
        'image_url'
    ];
}
