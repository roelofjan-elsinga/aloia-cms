<?php

namespace FlatFileCms;

use FlatFileCms\Commands\GenerateArticleFiles;
use FlatFileCms\Commands\NewArticle;
use Illuminate\Support\ServiceProvider;

class FlatFileCmsServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/flatfilecms.php', 'flatfilecms');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/flatfilecms.php' => config_path('flatfilecms.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateArticleFiles::class,
                NewArticle::class,
            ]);
        }
    }

}