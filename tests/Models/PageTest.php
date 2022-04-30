<?php


namespace AloiaCms\Tests\Models;

use AloiaCms\Facades\PageFacade;
use Carbon\Carbon;
use AloiaCms\Models\ContentBlock;
use AloiaCms\Models\Page;
use AloiaCms\Tests\TestCase;

class PageTest extends TestCase
{
    public function test_pages_need_to_have_a_title()
    {
        $this->expectExceptionMessage('Attribute title is required');

        Page::find('testing')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();
    }

    public function test_pages_data_file_is_updated()
    {
        $this->assertCount(0, Page::all());

        Page::find('testing')
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

        $this->assertCount(1, Page::all());
    }

    public function test_page_entry_is_made()
    {
        Page::find('testing')
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

        $this->assertTrue(Page::find('testing')->exists());
        $this->assertSame(date('Y-m-d'), Page::find('testing')->post_date);
    }

    public function test_null_is_returned_when_getting_non_existing_article()
    {
        $this->assertFalse(Page::find('blabla')->exists());
    }

    public function test_homepage_is_retrievable_using_a_static_method()
    {
        Page::find('testing')
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

        $homepage = Page::homepage();

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
    }

    public function test_page_url_is_file_name_when_not_specified()
    {
        Page::find('testing')
            ->setMatter([
                'title' => 'title',
                'description' => 'description',
                'summary' => 'summary',
                'template_name' => 'template',
                'is_published' => true,
                'is_scheduled' => false,
                'is_homepage' => true,
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Homepage')
            ->save();

        $homepage = Page::homepage();

        $this->assertSame('testing', $homepage->url());
    }

    public function test_can_get_all_published_pages()
    {
        Page::find('homepage')
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

        Page::find('contact')
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

        $published_pages = Page::published();

        $this->assertSame('Homepage title', $published_pages->first()->title());
    }

    public function test_pages_folder_is_created_when_non_existent()
    {
        $this->assertFalse($this->fs->hasChild('content/collections/pages'));

        $page = new Page();

        $page->getFolderPath();

        $this->assertTrue($this->fs->hasChild('content/collections/pages'));
    }

    public function test_page_can_be_deleted_by_slug()
    {
        Page::find('homepage')
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

        $this->assertTrue(Page::find('homepage')->exists());

        Page::find('homepage')->delete();

        $this->assertFalse(Page::find('homepage')->exists());
    }

    public function test_updated_page_results_in_a_different_date_than_post_date()
    {
        Page::find('homepage')
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

        $this->assertSame(date('Y-m-d'), Page::find('homepage')->getUpdateDate()->toDateString());
    }

    public function test_page_can_parse_content_block_in_content()
    {
        ContentBlock::find('testing')
            ->setBody('## Testing content blocks')
            ->save();

        $page = Page::find('testing')
            ->setExtension('html')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'post_date' => date('Y-m-d'),
                'is_published' => true,
                'is_scheduled' => false,
                'summary' => 'summary',
                'template_name' => 'default'
            ])
            ->setBody('<h1>Testing</h1> ===testing===')
            ->save();

        $this->assertStringContainsString('<h2>Testing content blocks</h2>', $page->body());
    }

    public function test_page_can_be_accessed_through_service_container()
    {
        Page::find('testing')
            ->setExtension('html')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'post_date' => date('Y-m-d'),
                'is_published' => true,
                'is_scheduled' => false,
                'summary' => 'summary',
                'template_name' => 'default'
            ])
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", trim(app(Page::class)->findById('testing')->body()));
    }

    public function test_page_can_be_accessed_through_facade()
    {
        Page::find('testing')
            ->setExtension('html')
            ->setMatter([
                'title' => 'Testing',
                'description' => 'Testing',
                'post_date' => date('Y-m-d'),
                'is_published' => true,
                'is_scheduled' => false,
                'summary' => 'summary',
                'template_name' => 'default'
            ])
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", trim(PageFacade::findById('testing')->body()));
    }
}
