<?php

namespace FlatFileCms\Models;

use FlatFileCms\Models\Contracts\ModelInterface;
use FlatFileCms\Models\Contracts\PublishInterface;
use FlatFileCms\Models\Traits\Postable;
use FlatFileCms\Models\Traits\Publishable;
use FlatFileCms\Models\Traits\Updatable;

class Page extends Model implements ModelInterface, PublishInterface
{
    use Publishable, Postable, Updatable;

    protected $folder = 'pages';
}
