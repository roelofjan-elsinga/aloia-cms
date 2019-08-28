<?php


namespace FlatFileCms\Tests;

use FlatFileCms\Page;
use Illuminate\Support\Collection;
use org\bovigo\vfs\vfsStream;

class PageTest extends TestCase
{
    public function test_pages_file_is_created_when_non_exists()
    {
        $this->assertFalse($this->fs->hasChild('content/pages.json'));

        Page::all();

        $this->assertTrue($this->fs->hasChild('content/pages.json'));
    }

    public function test_pages_need_to_have_a_title()
    {
        $this->expectExceptionMessage('Attribute title is required');

        Page::update(
            Page::raw()
                ->push(
                    [
                        'filename' => 'testing.md',
                        'postDate' => date('Y-m-d')
                    ]
                )
        );
    }

    public function test_pages_data_file_is_updated()
    {
        Page::all();

        $pages = json_decode($this->getFileContentsFromFilePath('content/pages.json'), true);

        $this->assertSame(0, count($pages));

        Page::update(
            Page::raw()
                ->push(
                    [
                        'title' => 'Testing',
                        'description' => 'Testing',
                        'summary' => 'Testing',
                        'template_name' => 'template',
                        'isPublished' => true,
                        'isScheduled' => false,
                        'filename' => 'testing.md',
                        'postDate' => date('Y-m-d')
                    ]
                )
        );

        $pages = json_decode($this->getFileContentsFromFilePath('content/pages.json'), true);

        $this->assertSame(1, count($pages));
    }

    public function test_article_entry_is_made()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Testing',
                    'description' => 'Testing',
                    'summary' => 'Testing',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $pages = json_decode($this->getFileContentsFromFilePath('content/pages.json'), true);

        $this->assertSame('testing.md', $pages[0]['filename']);
        $this->assertSame(date('Y-m-d'), $pages[0]['postDate']);
    }

    public function test_null_is_returned_when_getting_non_existing_article()
    {
        $article = Page::forSlug('blabla');

        $this->assertNull($article);
    }

    public function test_article_instance_is_returned_when_getting_existing_article_by_slug()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Testing',
                    'description' => 'Testing',
                    'summary' => 'Testing',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $file = vfsStream::url('root/content/pages/testing.md');
        file_put_contents($file, "# Testing");

        $article = Page::forSlug('testing');

        $this->assertNotNull($article);
        $this->assertSame('Testing', $article->title());
    }
}
