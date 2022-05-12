<?php

namespace AloiaCms\Models;

use AloiaCms\Models\Contracts\ModelInterface;

class MetaTag extends Model implements ModelInterface
{
    protected $required_fields = [
        'title',
        'description',
        'author',
        'image_url'
    ];
}
