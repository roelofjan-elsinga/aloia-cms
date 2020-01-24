<?php


namespace FlatFileCms\Tests\Console;

use FlatFileCms\Tests\TestCase;
use FlatFileCms\Models\Page;
use Illuminate\Support\Facades\Config;

class UpgradeZeroToOneCommandTest extends TestCase
{
    private function defaultPageConfig(): array
    {
        return [
            "title" => "This is a page title",
            "filename" => "page.md",
            "description" => "This page has content",
            "postDate" => "",
            "isPublished" => true,
            "isScheduled" => false,
            "summary" => "This page has content",
            "template_name" => "public",
            "updateDate" => "2019-09-14 15:36:04",
            "in_menu" => false,
            "is_homepage" => false,
            "image" => "",
            "category" => "home",
            "menu_name" => ""
        ];
    }

    public function testUpgradeCorrectlyMigratesArticles()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([$this->defaultPageConfig()], 128));
        file_put_contents(Config::get('flatfilecms.pages.folder_path') . '/page.md', "# This is content");

        $this
            ->artisan('flatfilecms:upgrade:0-to-1')
            ->execute();

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/pages/page.md'));
        $this->assertTrue(Page::fileExists('page'));
        $this->assertStringContainsString("title: 'This is a page title'", Page::open('page')->rawContent());
        $this->assertStringContainsString("This is a page title", Page::open('page')->title);
    }
}
