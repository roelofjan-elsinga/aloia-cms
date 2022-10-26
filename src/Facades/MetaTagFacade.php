<?php

namespace AloiaCms\Facades;

use AloiaCms\Models\MetaTag;
use Illuminate\Support\Facades\Facade;

class MetaTagFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MetaTag::class;
    }
}
