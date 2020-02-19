<?php


namespace AloiaCms\Tests;

use Carbon\Carbon;
use AloiaCms\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;

class ArticleTest extends TestCase
{
    public function test_articles_file_is_created_when_non_exists()
    {
        $this->assertFalse($this->fs->hasChild('content/articles.json'));

        Article::all();

        $this->assertTrue($this->fs->hasChild('content/articles.json'));
    }

    public function test_filename_is_required_to_create_article_config()
    {
        $this->expectExceptionMessage('Attribute filename is required');

        Article::update(
            Article::raw()
                ->push(
                    [
                        'postDate' => date('Y-m-d')
                    ]
                )
        );
    }

    public function test_post_date_is_required_to_create_article_config()
    {
        $this->expectExceptionMessage('Attribute postDate is required');

        Article::update(
            Article::raw()
                ->push(
                    [
                        'filename' => 'testing.md',
                    ]
                )
        );
    }

    public function test_articles_data_file_is_updated()
    {
        Article::all();

        $articles = json_decode($this->getFileContentsFromFilePath('content/articles.json'), true);

        $this->assertSame(0, count($articles));

        Article::update(
            Article::raw()
                ->push(
                    [
                        'filename' => 'testing.md',
                        'postDate' => date('Y-m-d')
                    ]
                )
        );

        $articles = json_decode($this->getFileContentsFromFilePath('content/articles.json'), true);

        $this->assertSame(1, count($articles));
    }

    public function test_article_entry_is_made()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $articles = json_decode($this->getFileContentsFromFilePath('content/articles.json'), true);

        $this->assertSame('testing.md', $articles[0]['filename']);
        $this->assertSame(date('Y-m-d'), $articles[0]['postDate']);
    }

    public function test_null_is_returned_when_getting_non_existing_article()
    {
        $article = Article::forSlug('blabla');

        $this->assertNull($article);
    }

    public function test_article_instance_is_returned_when_getting_existing_article_by_slug()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')->at($this->fs)->setContent('# Testing');

        $article = Article::forSlug('testing');

        $this->assertNotNull($article);
        $this->assertSame('Testing', $article->title());
        $this->assertSame('article', $article->type());
    }

    public function test_article_is_not_published_by_default()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $articles = Article::all();

        $this->assertSame(1, $articles->count());

        $articles = Article::published();

        $this->assertSame(0, $articles->count());
    }

    public function test_class_instance_is_statically_retrievable()
    {
        $this->assertSame(Article::class, get_class(Article::instance()));
    }

    public function test_slug_can_be_retrieved_as_partial_and_full_path()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')->at($this->fs)->setContent('# Testing');

        Config::set('aloiacms.articles.url_prefix', 'articles');

        $article = Article::forSlug('testing');

        $this->assertSame('testing', $article->slug());
        $this->assertSame('articles/testing', $article->slug(true));
    }

    public function test_article_file_type_returns_file_extension()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ],
                [
                    'filename' => 'contact.html',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->assertSame('md', Article::forSlug('testing')->fileType());
        $this->assertSame('html', Article::forSlug('contact')->fileType());
    }

    public function test_image_can_be_retrieved_from_the_content()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'image.md',
                    'postDate' => date('Y-m-d')
                ],
                [
                    'filename' => 'no-image.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/image.md')
            ->at($this->fs)
            ->setContent('# Image ![Placeholder](https://via.placeholder.com/150)');

        vfsStream::newFile('content/articles/no-image.md')
            ->at($this->fs)
            ->setContent('# Image');

        $this->assertSame('https://via.placeholder.com/150', Article::forSlug('image')->image());
        $this->assertEmpty(Article::forSlug('no-image')->image());
    }

    public function test_thumbnail_is_returned_when_specified_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'thumbnail' => 'https://via.placeholder.com/150'
                ]
            ])
        );

        $this->assertSame('https://via.placeholder.com/150', Article::forSlug('testing')->thumbnail());
    }

    public function test_thumbnail_is_generated_from_image_when_not_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Image ![Placeholder](https://via.placeholder.com/150.jpg)');

        $this->assertSame('/images/articles/150_w300.jpg', Article::forSlug('testing')->thumbnail());
    }

    public function test_empty_thumbnail_is_returned_when_no_t_humbnail_and_image_are_available()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Image');

        $this->assertEmpty(Article::forSlug('testing')->thumbnail());
    }

    public function test_post_date_can_be_retrieved_as_formatted_string_and_date_object()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $page = Article::forSlug('testing');

        $this->assertSame(date('F jS, Y'), $page->postDate());
        $this->assertSame(date('F jS, Y'), $page->updatedDate());
    }

    public function test_update_date_is_returned_when_specified_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => Carbon::now()->subWeek()->toDateString(),
                    'updateDate' => Carbon::now()->toDateTimeString(),
                ]
            ])
        );

        $page = Article::forSlug('testing');

        $this->assertFalse($page->rawPostDate()->isSameDay($page->rawUpdatedDate()));
    }

    public function test_malformed_update_date_returns_the_post_date()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => Carbon::now()->subWeek()->toDateString(),
                    'updateDate' => Carbon::now()->toDateString(),
                ]
            ])
        );

        $page = Article::forSlug('testing');

        $this->assertTrue($page->rawPostDate()->isSameDay($page->rawUpdatedDate()));
    }

    public function test_raw_content_can_be_retrieved_for_editors()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Testing');

        $this->assertSame('# Testing', Article::forSlug('testing')->rawContent());
    }

    public function test_description_is_returned_when_specified_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'description' => 'Testing the description'
                ]
            ])
        );

        $this->assertSame('Testing the description', Article::forSlug('testing')->description());
    }

    public function test_description_is_generated_from_content_when_not_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Testing 
This is a paragraph');

        $this->assertSame('This is a paragraph', Article::forSlug('testing')->description());
    }

    public function test_description_is_empty_when_no_paragraphs_found()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Testing');

        $this->assertEmpty(Article::forSlug('testing')->description());
    }

    public function test_canonical_link_is_returned_when_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'canonical' => 'https://www.google.com'
                ]
            ])
        );

        $this->assertSame('https://www.google.com', Article::forSlug('testing')->canonicalLink());
    }

    public function test_canonical_link_is_null_when_not_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->assertNull(Article::forSlug('testing')->canonicalLink());
    }

    public function test_external_url_is_returned_when_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'url' => 'https://www.google.com'
                ]
            ])
        );

        $this->assertSame('https://www.google.com', Article::forSlug('testing')->url());
    }

    public function test_url_is_null_when_not_specified()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->assertNull(Article::forSlug('testing')->url());
    }

    public function test_article_is_not_marked_as_scheduled_when_not_set_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->assertFalse(Article::forSlug('testing')->isScheduled());
    }

    public function test_article_is_not_marked_as_scheduled_when_specified_as_so_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'isScheduled' => false
                ]
            ])
        );

        $this->assertFalse(Article::forSlug('testing')->isScheduled());
    }

    public function test_article_is_marked_as_scheduled_when_specified_as_so_in_config()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'isScheduled' => true
                ]
            ])
        );

        $this->assertTrue(Article::forSlug('testing')->isScheduled());
    }

    public function test_articles_folder_path_is_created_when_not_existent()
    {
        Config::set('aloiacms.articles.folder_path', $this->fs->getChild('content')->url() . '/new-articles');

        $this->assertFalse($this->fs->hasChild('content/new-articles'));

        Article::all();

        $this->assertTrue($this->fs->hasChild('content/new-articles'));
    }

    public function test_title_can_be_retrieved_through_getter_and_method()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        vfsStream::newFile('content/articles/testing.md')
            ->at($this->fs)
            ->setContent('# Testing');

        $this->assertSame('Testing', Article::forSlug('testing')->title());
        $this->assertSame('Testing', Article::forSlug('testing')->title);
        $this->assertNull(Article::forSlug('testing')->titles);
    }

    public function test_article_can_be_removed_by_slug()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ],
                [
                    'filename' => 'contact.html',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        file_put_contents(vfsStream::url('root/content/articles/testing.md'), '# Testing');
        file_put_contents(vfsStream::url('root/content/articles/contact.html'), '<h1>Contact</h1>');

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
        $this->assertTrue($this->fs->hasChild('content/articles/contact.html'));
        $this->assertCount(2, Article::all());

        Article::deleteBySlug('contact');

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
        $this->assertFalse($this->fs->hasChild('content/articles/contact.html'));
        $this->assertCount(1, Article::all());
    }
}
