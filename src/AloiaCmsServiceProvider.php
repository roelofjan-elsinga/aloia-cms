<?php

namespace AloiaCms;

use AloiaCms\Console\ConfigCommand;
use AloiaCms\Console\Migrations\UpgradeZeroToOneCommand;
use AloiaCms\Console\NewArticle;
use AloiaCms\Console\PermissionsCommand;
use AloiaCms\Models\ContentBlock;
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
                PermissionsCommand::class,
                UpgradeZeroToOneCommand::class,
            ]);
        }
    }

    /**
     * Bind the facades used by this package
     */
    private function bindFacades()
    {
        $this->app->bind('AloiaCmsBlock', function () {
            return new ContentBlock();
        });
    }
}
