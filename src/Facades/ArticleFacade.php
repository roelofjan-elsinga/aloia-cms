<?php

namespace AloiaCms\Facades;

use AloiaCms\Models\Article;
use Illuminate\Support\Facades\Facade;

class ArticleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Article::class;
    }
}
