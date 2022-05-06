<?php

namespace AloiaCms\Tests\Console;

use AloiaCms\Models\Article;
use AloiaCms\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class SitemapCommandTest extends TestCase
{
    public function test_sitemap_can_be_generated_through_command()
    {
        Article::find('test')
            ->setPostDate(now())
            ->save();

        $this
            ->artisan('aloiacms:sitemap')
            ->assertExitCode(0);

        $sitemap_path = Config::get('aloiacms.seo.sitemap_path');

        $this->assertFileExists($sitemap_path);

        $this->assertTrue(str_contains(file_get_contents($sitemap_path), "http://localhost/articles/test"));
    }
}
