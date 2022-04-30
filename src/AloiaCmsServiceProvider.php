<?php

namespace AloiaCms;

use AloiaCms\Console\ConfigCommand;
use AloiaCms\Console\NewArticle;
use AloiaCms\Models\Article;
use AloiaCms\Models\ContentBlock;
use AloiaCms\Models\MetaTag;
use AloiaCms\Models\Page;
use Illuminate\Support\ServiceProvider;

class AloiaCmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aloiacms.php', 'aloiacms');

        $this->bindFacades();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/aloiacms.php' => config_path('aloiacms.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                NewArticle::class,
                ConfigCommand::class,
            ]);
        }
    }

    /**
     * Bind the facades used by this package
     */
    private function bindFacades()
    {
        $this->app->bind(Article::class, function () {
            return new Article();
        });

        $this->app->bind(ContentBlock::class, function () {
            return new ContentBlock();
        });

        $this->app->bind(MetaTag::class, function () {
            return new MetaTag();
        });

        $this->app->bind(Page::class, function () {
            return new Page();
        });
    }
}
