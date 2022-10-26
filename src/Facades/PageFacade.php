<?php

namespace AloiaCms\Facades;

use AloiaCms\Models\Page;
use Illuminate\Support\Facades\Facade;

class PageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Page::class;
    }
}
