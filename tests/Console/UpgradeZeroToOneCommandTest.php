<?php


namespace FlatFileCms\Tests\Console;

use FlatFileCms\Models\ContentBlock;
use FlatFileCms\Tests\TestCase;
use FlatFileCms\Models\Page;
use FlatFileCms\Models\Article;
use FlatFileCms\Models\MetaTag;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;

class UpgradeZeroToOneCommandTest extends TestCase
{
    public function testCommandCreatesCollectionFolderIfNonExisting()
    {
        $content_path = vfsStream::url('root/content/objects');

        Config::set('flatfilecms.collections_path', $content_path);

        $this->artisan('flatfilecms:upgrade:0-to-1')
            ->expectsQuestion("The collections folder doesn't exist, do you want to create it at {$content_path}?", true);

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path')));
    }

    public function testUpgradeCorrectlyMigratesPages()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([$this->defaultPageConfig()], 128));
        file_put_contents(Config::get('flatfilecms.pages.folder_path') . '/page.md', "# This is content");

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/pages/page.md'));
        $this->assertTrue(Page::find('page')->exists());
        $this->assertStringContainsString("title: 'This is a page title'", Page::find('page')->rawContent());
        $this->assertStringContainsString("This is a page title", Page::find('page')->title);
    }

    public function testUpgradeCorrectlyMigratesArticles()
    {
        file_put_contents(Config::get('flatfilecms.articles.file_path'), json_encode([$this->defaultArticleConfig()], 128));
        file_put_contents(Config::get('flatfilecms.articles.folder_path') . '/article.md', "# This is content");

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/articles/article.md'));
        $this->assertTrue(Article::find('article')->exists());
        $this->assertStringContainsString("url: article", Article::find('article')->rawContent());
        $this->assertStringContainsString("Article description", Article::find('article')->description);
        $this->assertSame("https://google.com", Article::find('article')->external_url);
    }

    public function testUpgradeCorrectlyMigratesContentBlocks()
    {
        file_put_contents(Config::get('flatfilecms.content_blocks.folder_path') . '/content.md', "# This is content");

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/content_blocks/content.md'));
        $this->assertTrue(ContentBlock::find('content')->exists());
        $this->assertStringContainsString("identifier: content", ContentBlock::find('content')->rawContent());
    }

    public function test_content_blocks_are_skipped_if_folder_does_not_exist()
    {
        $content_path = vfsStream::url('root/content/objects');

        Config::set('flatfilecms.content_blocks.folder_path', $content_path);

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertFalse(file_exists($content_path));
    }

    public function testUpgradeCorrectlyMigratesMetaTags()
    {
        file_put_contents(Config::get('flatfilecms.meta_tags.file_path'), json_encode(["default" => $this->defaultMetaTagConfig()], 128));

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/meta_tags/default.md'));
        $this->assertTrue(MetaTag::find('default')->exists());
        $this->assertStringContainsString("title: 'Page title'", MetaTag::find('default')->rawContent());
        $this->assertStringContainsString("Page description", MetaTag::find('default')->description);
    }

    public function testUpgradeWritesTaxonomyAsUrlToPages()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([$this->defaultPageConfig()], 128));
        file_put_contents(Config::get('flatfilecms.pages.folder_path') . '/page.md', "# This is content");
        file_put_contents(Config::get('flatfilecms.taxonomy.file_path'), json_encode([$this->defaultTaxonomyConfig()], 128));

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/pages/page.md'));
        $this->assertTrue(Page::find('page')->exists());
        $this->assertStringContainsString("url: /page", Page::find('page')->rawContent());
        $this->assertStringContainsString("/page", Page::find('page')->url);
    }

    public function testUpgradeWritesNestedTaxonomyAsUrlToPages()
    {
        $page_content = $this->defaultPageConfig();
        $page_content['category'] = 'testing';

        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([$page_content], 128));
        file_put_contents(Config::get('flatfilecms.pages.folder_path') . '/page.md', "# This is content");
        file_put_contents(Config::get('flatfilecms.taxonomy.file_path'), json_encode($this->defaultNestedTaxonomyConfig(), 128));

        $this->artisan('flatfilecms:upgrade:0-to-1');

        $this->assertTrue(file_exists(Config::get('flatfilecms.collections_path') . '/pages/page.md'));
        $this->assertTrue(Page::find('page')->exists());
        $this->assertStringContainsString("url: /testing/page", Page::find('page')->rawContent());
        $this->assertStringContainsString("/testing/page", Page::find('page')->url);
    }

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

    private function defaultArticleConfig()
    {
        return [
            "filename" => "article.md",
            "postDate" => "2020-01-01",
            "updateDate" => "2020-01-01 12:00:00",
            "description" => "Article description",
            "isScheduled" => false,
            "isPublished" => false,
            'url' => 'https://google.com'
        ];
    }

    private function defaultMetaTagConfig()
    {
        return [
            "title" => "Page title",
            "keywords" => "Keywords go here",
            "description" => "Page description",
            "author" => "Author name",
            "image_small" => "https://roelofjanelsinga.com/images/logo/logo_banner.jpg",
            "image_large" => "https://roelofjanelsinga.com/images/logo/logo_banner.jpg"
        ];
    }

    private function defaultTaxonomyConfig()
    {
        return [
            "category_url_prefix" => "",
            "category_name" => "home",
            "parent_category" => null
        ];
    }

    protected function defaultNestedTaxonomyConfig()
    {
        $taxonomy = [$this->defaultTaxonomyConfig()];

        $taxonomy[] = [
            "category_url_prefix" => "testing",
            "category_name" => "testing",
            "parent_category" => "home"
        ];

        return $taxonomy;
    }
}
