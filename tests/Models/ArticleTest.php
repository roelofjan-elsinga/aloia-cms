<?php


namespace FlatFileCms\Tests\Models;

use Carbon\Carbon;
use FlatFileCms\Models\Article;
use FlatFileCms\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;

class ArticleTest extends TestCase
{
    public function test_filename_is_required_to_create_article_config()
    {
        $this->expectExceptionMessage('Filename is required');

        $article = new Article();

        $article
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();
    }

    public function test_post_date_is_required_to_create_article_config()
    {
        $this->expectExceptionMessage('Attribute post_date is required');

        Article::find('testing')->save();
    }

    public function test_articles_is_created_when_not_existing()
    {
        $this->assertFalse(Article::find('article')->exists());

        Article::find('article')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();

        $this->assertTrue(Article::find('article')->exists());
        $this->assertSame(date('Y-m-d'), Article::find('article')->post_date);
    }

    public function test_null_is_returned_when_getting_non_existing_article()
    {
        $this->assertFalse(Article::find('blabla')->exists());

        Article::find('blabla')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();

        $this->assertTrue(Article::find('blabla')->exists());
    }

    public function test_article_is_not_published_by_default()
    {
        Article::find('testing')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();

        $articles = Article::all();

        $this->assertSame(1, $articles->count());

        $articles = Article::published();

        $this->assertSame(0, $articles->count());
    }

    public function test_article_file_type_returns_file_extension()
    {
        Article::find('testing')
            ->setExtension('md')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();

        Article::find('contact')
            ->setExtension('html')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->save();

        $this->assertSame('md', Article::find('testing')->extension());
        $this->assertSame('html', Article::find('contact')->extension());
    }

    public function test_image_can_be_retrieved_from_the_content()
    {
        Article::find('image')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->setBody('# Image ![Placeholder](https://via.placeholder.com/150)')
            ->save();

        Article::find('no-image')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->setBody('# Image')
            ->save();

        $this->assertSame('https://via.placeholder.com/150', Article::find('image')->image());
        $this->assertEmpty(Article::find('no-image')->image());
    }

    public function test_image_is_retrieved_from_front_matter_if_present()
    {
        Article::find('image')
            ->setMatter(['post_date' => date('Y-m-d'), 'image' => 'https://google.com/image.jpeg'])
            ->setBody('# Image ![Placeholder](https://via.placeholder.com/150)')
            ->save();

        $this->assertSame('https://google.com/image.jpeg', Article::find('image')->image());
    }

    public function test_thumbnail_is_returned_when_specified_in_config()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'thumbnail' => 'https://via.placeholder.com/150'
            ])
            ->setBody('# Image')
            ->save();

        $this->assertSame('https://via.placeholder.com/150', Article::find('testing')->thumbnail());
    }

    public function test_thumbnail_is_generated_from_image_when_not_specified()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
            ])
            ->setBody('# Image ![Placeholder](https://via.placeholder.com/150.jpg)')
            ->save();

        $this->assertSame('/images/articles/150_w300.jpg', Article::find('testing')->thumbnail());
    }

    public function test_empty_thumbnail_is_returned_when_no_thumbnail_and_image_are_available()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
            ])
            ->setBody('# Image')
            ->save();

        $this->assertEmpty(Article::find('testing')->thumbnail());
    }

    public function test_update_date_is_returned_when_specified_in_config()
    {
        $update_date = Carbon::now();

        Article::find('testing')
            ->setPostDate(Carbon::now()->subWeek())
            ->setUpdateDate($update_date)
            ->save();

        $article = Article::find('testing');

        $this->assertSame($article->getUpdateDate()->toDateTimeString(), $update_date->toDateTimeString());
    }

    public function test_raw_content_can_be_retrieved_for_editors()
    {
        Article::find('testing')
            ->setMatter(['post_date' => date('Y-m-d')])
            ->setBody('# Testing')
            ->save();

        $this->assertStringContainsString('# Testing', Article::find('testing')->rawBody());
    }

    public function test_description_is_returned_when_specified_in_config()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'description' => 'Testing the description'
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertSame('Testing the description', Article::find('testing')->description);
    }

    public function test_description_is_generated_from_content_when_not_specified()
    {
        $content = '# Testing 
This is a paragraph';

        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody($content)
            ->save();

        $this->assertSame('This is a paragraph', Article::find('testing')->description());
    }

    public function test_description_is_empty_when_no_paragraphs_found()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertEmpty(Article::find('testing')->description());
    }

    public function test_canonical_link_is_returned_when_specified()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'canonical' => 'https://www.google.com'
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertSame('https://www.google.com', Article::find('testing')->canonical);
        $this->assertSame('https://www.google.com', Article::find('testing')->canonicalLink());
    }

    public function test_canonical_link_is_null_when_not_specified()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertNull(Article::find('testing')->canonical);
        $this->assertNull(Article::find('testing')->canonicalLink());
    }

    public function test_external_url_is_returned_when_specified()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'external_url' => 'https://www.google.com'
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertSame('https://www.google.com', Article::find('testing')->external_url);
        $this->assertSame('https://www.google.com', Article::find('testing')->externalUrl());
    }

    public function test_url_is_null_when_not_specified()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertNull(Article::find('testing')->external_url);
        $this->assertNull(Article::find('testing')->externalUrl());
    }

    public function test_article_is_not_marked_as_scheduled_when_not_set_in_config()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertFalse(Article::find('testing')->isScheduled());
    }

    public function test_article_is_not_marked_as_scheduled_when_specified_as_so_in_config()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'is_scheduled' => false
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertFalse(Article::find('testing')->isScheduled());
    }

    public function test_article_is_marked_as_scheduled_when_specified_as_so_in_config()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'is_scheduled' => true
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertTrue(Article::find('testing')->isScheduled());
    }

    public function test_articles_folder_path_is_created_when_not_existent()
    {
        $this->assertFalse($this->fs->hasChild('content/collections/articles'));

        $article = new Article();

        $article->getFolderPath();

        $this->assertTrue($this->fs->hasChild('content/collections/articles'));
    }

    public function test_title_can_be_retrieved_through_getter_and_method()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'is_scheduled' => true,
                'title' => 'Testing things'
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertSame('Testing things', Article::find('testing')->title());
        $this->assertSame('Testing things', Article::find('testing')->title);

        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'is_scheduled' => true
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertSame('Testing', Article::find('testing')->title());
    }

    public function test_article_can_be_removed_by_slug()
    {
        Article::find('testing')
            ->setMatter([
                'post_date' => date('Y-m-d')
            ])
            ->setBody('# Testing')
            ->save();

        $this->assertTrue(Article::find('testing')->exists());

        Article::find('testing')->delete();

        $this->assertFalse(Article::find('testing')->exists());
    }

    public function test_can_get_all_articles()
    {
        Article::find('article')
            ->setMatter(['title' => 'Article title', 'post_date' => date('Y-m-d')])
            ->setBody('# This is content')
            ->save();

        $articles = Article::all();

        $this->assertCount(1, $articles);
        $this->assertSame('Article title', $articles->first()->title);
    }

    public function test_slug_is_same_as_file_name()
    {
        $article = Article::find('article')
            ->setMatter(['title' => 'Article title', 'post_date' => date('Y-m-d')])
            ->setBody('# This is content')
            ->save();

        $this->assertSame('article', $article->slug());
    }

    public function test_post_date_can_be_retrieved()
    {
        $path = vfsStream::url('root/content/collections/articles/testing.md');

        mkdir(vfsStream::url('root/content/collections/articles'));

        file_put_contents(
            $path,
            file_get_contents(getcwd() . '/tests/stubs/article_without_post_and_update_date.stub'),
            FILE_APPEND
        );

        $this->assertNull(Article::find('testing')->getPostDate());
        $this->assertNull(Article::find('testing')->getUpdateDate());
    }

    public function test_scheduled_articles_can_be_retrieved()
    {
        Article::find('testing_scheduled')
            ->setMatter(['post_date' => date('Y-m-d'), 'is_scheduled' => true])
            ->save();

        Article::find('testing_unscheduled')
            ->setMatter(['post_date' => date('Y-m-d'), 'is_scheduled' => false])
            ->save();

        $articles = Article::all();

        $this->assertSame(2, $articles->count());

        $articles = Article::scheduled();

        $this->assertSame(1, $articles->count());
    }

    public function test_front_matter_is_retrievable_as_array()
    {
        $article = Article::find('article')
            ->setMatter(['title' => 'Article title', 'post_date' => date('Y-m-d')])
            ->setBody('# This is content')
            ->save();

        $this->assertArrayHasKey('title', $article->matter());
        $this->assertArrayHasKey('post_date', $article->matter());
    }
}
