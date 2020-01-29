<?php


namespace FlatFileCms\Tests;

use Carbon\Carbon;
use FlatFileCms\Models\ContentBlock;
use FlatFileCms\Page;
use FlatFileCms\Taxonomy\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;

class PageTest extends TestCase
{
    public function test_pages_need_to_have_a_title()
    {
        $this->expectExceptionMessage('Attribute title is required');

        \FlatFileCms\Models\Page::find('testing')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();
    }

    public function test_pages_data_file_is_updated()
    {
        $this->assertCount(0, \FlatFileCms\Models\Page::all());

        \FlatFileCms\Models\Page::find('testing')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'post_date' => date('Y-m-d')
            ])
            ->save();

        $this->assertCount(1, \FlatFileCms\Models\Page::all());
    }

    public function test_page_entry_is_made()
    {
        \FlatFileCms\Models\Page::find('testing')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'post_date' => date('Y-m-d')
            ])
            ->save();

        $this->assertTrue(\FlatFileCms\Models\Page::find('testing')->exists());
        $this->assertSame(date('Y-m-d'), \FlatFileCms\Models\Page::find('testing')->post_date);
    }

    public function test_null_is_returned_when_getting_non_existing_article()
    {
        $this->assertFalse(\FlatFileCms\Models\Page::find('blabla')->exists());
    }

    public function test_homepage_is_retrievable_using_a_static_method()
    {
        \FlatFileCms\Models\Page::find('testing')
            ->setMatter([
                'title' => 'Homepage title',
                'description' => 'Homepage description',
                'summary' => 'Homepage summary',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'is_homepage' => true,
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Homepage')
            ->save();

        $homepage = \FlatFileCms\Models\Page::homepage();

        $this->assertSame('Homepage title', $homepage->title());
        $this->assertSame('Homepage description', $homepage->description());
        $this->assertSame('Homepage summary', $homepage->summary());
        $this->assertSame('template', $homepage->templateName());
        $this->assertSame('website', $homepage->type());
        $this->assertSame('md', $homepage->extension());
        $this->assertSame("", $homepage->image());
        $this->assertSame("", $homepage->thumbnail());
        $this->assertSame(date('F jS, Y'), $homepage->getPostDate()->format('F jS, Y'));
        $this->assertTrue($homepage->isPublished());
        $this->assertFalse($homepage->isScheduled());
        $this->assertFalse($homepage->isInMenu());
        $this->assertSame('Homepage title', $homepage->menuName());
        $this->assertSame('<h1>Homepage</h1>', $homepage->body());
        $this->assertStringContainsString('# Homepage', $homepage->rawBody());
        $this->assertNull($homepage->metaData());
        $this->assertNull($homepage->canonicalLink());
        $this->assertEmpty($homepage->author());
        $this->assertEmpty($homepage->keywords());
    }

    public function test_can_get_all_published_pages()
    {
        \FlatFileCms\Models\Page::find('homepage')
            ->setMatter([
                'title' => 'Homepage title',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Homepage')
            ->save();

        \FlatFileCms\Models\Page::find('contact')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => false,
                'is_scheduled' => false,
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Contact')
            ->save();

        $published_pages = \FlatFileCms\Models\Page::published();

        $this->assertSame('Homepage title', $published_pages->first()->title());
    }

    public function test_pages_folder_is_created_when_non_existent()
    {
        $this->assertFalse($this->fs->hasChild('content/collections/pages'));

        $page = new \FlatFileCms\Models\Page();

        $page->getFolderPath();

        $this->assertTrue($this->fs->hasChild('content/collections/pages'));
    }

    public function test_page_can_be_deleted_by_slug()
    {
        \FlatFileCms\Models\Page::find('homepage')
            ->setMatter([
                'title' => 'Homepage title',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Homepage')
            ->save();

        $this->assertTrue(\FlatFileCms\Models\Page::find('homepage')->exists());

        \FlatFileCms\Models\Page::find('homepage')->delete();

        $this->assertFalse(\FlatFileCms\Models\Page::find('homepage')->exists());
    }

    public function test_updated_page_results_in_a_different_date_than_post_date()
    {
        \FlatFileCms\Models\Page::find('homepage')
            ->setMatter([
                'title' => 'Homepage title',
                'description' => 'Testing',
                'summary' => 'Testing',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false
            ])
            ->setPostDate(Carbon::now()->subWeek())
            ->setUpdateDate(Carbon::now())
            ->setBody('# Homepage')
            ->save();

        $this->assertSame(date('Y-m-d'), \FlatFileCms\Models\Page::find('homepage')->getUpdateDate()->toDateString());
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
}
