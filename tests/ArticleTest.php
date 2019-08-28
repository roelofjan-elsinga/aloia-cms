<?php


namespace FlatFileCms\Tests;

use FlatFileCms\Article;
use Illuminate\Support\Collection;
use org\bovigo\vfs\vfsStream;

class ArticleTest extends TestCase
{
    public function test_articles_file_is_created_when_non_exists()
    {
        $this->assertFalse($this->fs->hasChild('content/articles.json'));

        Article::all();

        $this->assertTrue($this->fs->hasChild('content/articles.json'));
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

        $file = vfsStream::url('root/content/articles/testing.md');
        file_put_contents($file, "# Testing");

        $article = Article::forSlug('testing');

        $this->assertNotNull($article);
        $this->assertSame('Testing', $article->title());
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
}
