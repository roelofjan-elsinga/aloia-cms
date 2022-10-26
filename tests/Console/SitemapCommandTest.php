<?php

use AloiaCms\Models\Article;
use Illuminate\Support\Facades\Config;

test('sitemap can be generated through the command', function () {
    Article::find('test')
        ->setPostDate(now())
        ->save();

    $this
        ->artisan('aloiacms:sitemap')
        ->assertExitCode(0);

    $sitemap_path = Config::get('aloiacms.seo.sitemap_path');

    $this->assertFileExists($sitemap_path);

    $this->assertTrue(str_contains(file_get_contents($sitemap_path), "http://localhost/articles/test"));
});
