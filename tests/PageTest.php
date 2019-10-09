<?php


namespace FlatFileCms\Tests;

use Carbon\Carbon;
use FlatFileCms\Page;
use FlatFileCms\Taxonomy\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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

    public function test_class_instance_is_statically_retrievable()
    {
        $this->assertSame(Page::class, get_class(Page::instance()));
    }

    public function test_homepage_is_retrievable_using_a_static_method()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'is_homepage' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/pages/homepage.md')
            ->at($this->fs)
            ->setContent('# Homepage');

        $homepage = Page::homepage();

        $this->assertSame('Homepage title', $homepage->title());
        $this->assertSame('Homepage description', $homepage->description());
        $this->assertSame('Homepage summary', $homepage->summary());
        $this->assertSame('template', $homepage->templateName());
        $this->assertSame('website', $homepage->type());
        $this->assertSame('md', $homepage->fileType());
        $this->assertSame('homepage.md', $homepage->filename());
        $this->assertSame("", $homepage->image());
        $this->assertSame("", $homepage->thumbnail());
        $this->assertSame(date('F jS, Y'), $homepage->postDate());
        $this->assertSame(date('F jS, Y'), $homepage->updatedDate());
        $this->assertTrue($homepage->isPublished());
        $this->assertFalse($homepage->isScheduled());
        $this->assertFalse($homepage->isInMenu());
        $this->assertSame('Homepage title', $homepage->menuName());
        $this->assertSame('<h1>Homepage</h1>', $homepage->content());
        $this->assertSame('# Homepage', $homepage->rawContent());
        $this->assertNull($homepage->sidebar());
        $this->assertNull($homepage->metaData());
        $this->assertNull($homepage->canonicalLink());
        $this->assertEmpty($homepage->author());
        $this->assertEmpty($homepage->keywords());
        $this->assertSame('home', $homepage->category());
    }

    public function test_can_get_all_published_pages()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'is_homepage' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d')
                ],
                [
                    'title' => 'Contact title',
                    'description' => 'Contact description',
                    'summary' => 'Contact summary',
                    'template_name' => 'template',
                    'isPublished' => false,
                    'is_homepage' => false,
                    'isScheduled' => false,
                    'filename' => 'contact.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/pages/homepage.md')
            ->at($this->fs)
            ->setContent('# Homepage');

        vfsStream::newFile('content/pages/contact.md')
            ->at($this->fs)
            ->setContent('# Contact');

        $published_pages = Page::published();

        $this->assertSame('Homepage title', $published_pages->first()->title());
    }

    public function test_pages_folder_is_created_when_non_existent()
    {
        Config::set('flatfilecms.pages.folder_path', $this->fs->getChild('content')->url() . '/test-pages');

        Page::all();

        $this->assertTrue($this->fs->hasChild('content/test-pages'));
    }

    public function test_page_title_is_resolved_from_content_when_incomplete_config()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([
            [
                'description' => 'No title was given',
                'filename' => 'homepage.md',
            ]
        ], JSON_PRETTY_PRINT));

        vfsStream::newFile('content/pages/homepage.md')
            ->at($this->fs)
            ->setContent('# Homepage');

        $page = Page::forSlug('homepage');

        $this->assertSame('Homepage', $page->title());
    }

    public function test_sidebar_can_be_parsed()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'is_homepage' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'meta_data' => [
                        'sidebar' => '===testing==='
                    ]
                ]
            ])
        );

        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/testing.html", "<h1>Testing</h1>");

        $sidebar = Page::forSlug('homepage')->sidebar();

        $this->assertSame('<div><h1>Testing</h1></div>', $sidebar);

        $sidebar = Page::forSlug('homepage')->rawSidebar();

        $this->assertSame('===testing===', $sidebar);
    }

    public function test_raw_sidebar_returns_null_when_not_filled_in()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'is_homepage' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->assertNull(Page::forSlug('homepage')->rawSidebar());
    }

    public function test_page_can_be_deleted_by_slug()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'is_homepage' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d')
                ],
                [
                    'title' => 'Contact title',
                    'description' => 'Contact description',
                    'summary' => 'Contact summary',
                    'template_name' => 'template',
                    'isPublished' => false,
                    'is_homepage' => false,
                    'isScheduled' => false,
                    'filename' => 'contact.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        file_put_contents(vfsStream::url('root/content/pages/homepage.md'), '# Testing');
        file_put_contents(vfsStream::url('root/content/pages/contact.md'), '# Contact');

        $this->assertTrue($this->fs->hasChild('content/pages/homepage.md'));
        $this->assertTrue($this->fs->hasChild('content/pages/contact.md'));
        $this->assertCount(2, Page::all());

        Page::deleteBySlug('contact');

        $this->assertTrue($this->fs->hasChild('content/pages/homepage.md'));
        $this->assertFalse($this->fs->hasChild('content/pages/contact.md'));
        $this->assertCount(1, Page::all());
    }
    
    public function test_slug_can_be_retrieved_with_category_prefix()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'category' => 'content-pages'
                ]
            ])
        );

        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'content-pages',
            'category_url_prefix' => 'content-pages',
            'parent_category' => 'home',
            'children' => []
        ]);

        $this->assertSame('content-pages/homepage', Page::forSlug('homepage')->slug(true));
    }
    
    public function test_fetching_the_taxonomy_of_a_page_without_category_results_in_default_taxonomy()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                ]
            ])
        );

        $taxonomy = Page::forSlug('homepage')->taxonomy();

        $this->assertSame('home', $taxonomy->name());
        $this->assertSame('', $taxonomy->fullUrl());
    }

    public function test_taxonomy_can_be_fetched()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'category' => 'content-pages'
                ]
            ])
        );

        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'content-pages',
            'category_url_prefix' => 'content-pages',
            'parent_category' => 'home',
            'children' => []
        ]);

        $taxonomy = Page::forSlug('homepage')->taxonomy();

        $this->assertSame('content-pages', $taxonomy->name());
        $this->assertSame('content-pages', $taxonomy->fullUrl());
    }

    public function test_malformed_post_date_results_into_todays_date()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([
            [
                'description' => 'No title was given',
                'filename' => 'homepage.md',
            ]
        ], JSON_PRETTY_PRINT));

        $page = Page::forSlug('homepage');

        $this->assertTrue($page->rawPostDate()->isToday());
    }

    public function test_updated_page_results_in_a_different_date_than_post_date()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([
            [
                'description' => 'No title was given',
                'filename' => 'homepage.md',
                'postDate' => Carbon::now()->subWeek()->toDateString(),
                'updateDate' => Carbon::now()->toDateTimeString()
            ]
        ], JSON_PRETTY_PRINT));

        $page = Page::forSlug('homepage');

        $this->assertTrue($page->rawPostDate()->isSameDay(Carbon::now()->subWeek()));
        $this->assertTrue($page->rawUpdatedDate()->isToday());
    }

    public function test_malformed_updated_date_will_result_in_the_post_date()
    {
        file_put_contents(Config::get('flatfilecms.pages.file_path'), json_encode([
            [
                'description' => 'No title was given',
                'filename' => 'homepage.md',
                'postDate' => Carbon::now()->subWeek()->toDateString(),
                'updateDate' => Carbon::now()->toDateString()
            ]
        ], JSON_PRETTY_PRINT));

        $page = Page::forSlug('homepage');

        $this->assertTrue($page->rawPostDate()->isSameDay($page->rawUpdatedDate()));
    }

    public function test_page_with_invalid_taxonomy_returns_as_having_root_as_category()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'category' => 'pages'
                ]
            ])
        );

        $this->assertSame('testing', Page::forSlug('testing')->slug(true));
    }
}
