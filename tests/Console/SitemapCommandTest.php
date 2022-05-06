<?php

namespace AloiaCms\Tests\Console;

use AloiaCms\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class SitemapCommandTest extends TestCase
{
    public function test_config_can_be_published_through_command()
    {
        $this
            ->artisan('aloiacms:sitemap')
            ->assertExitCode(0);

        $this->assertFileExists(Config::get('aloiacms.seo.sitemap_path'));
    }
}
