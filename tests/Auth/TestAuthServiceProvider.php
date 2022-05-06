<?php

namespace AloiaCms\Tests\Auth;

use AloiaCms\Auth\AloiaCmsUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TestAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Auth::provider('aloiacms', function ($app, array $config) {
            return new AloiaCmsUserProvider(
                $app['hash'],
                $config['model'],
            );
        });
    }
}
