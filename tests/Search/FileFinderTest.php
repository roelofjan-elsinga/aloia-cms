<?php


namespace FlatFileCms\Tests\Search;

use FlatFileCms\Article;
use FlatFileCms\Search\FileFinder;
use FlatFileCms\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class FileFinderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Using the real filesystem here,
        // because the native linux command doesn't recognize the virtual filesystem

        $content_path = __DIR__.'/test';

        if (!is_dir($content_path)) {
            mkdir($content_path, 0777, true);
        }

        Config::set('flatfilecms.articles.folder_path', $content_path);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->recursively_remove_directory(__DIR__.'/test');
    }

    public function test_no_results_are_returned_when_no_matching_files_found()
    {
        $results = FileFinder::find(Article::instance(), 'testing');

        $this->assertCount(0, $results);
    }

    public function test_only_files_with_matching_content_are_found_for_search_terms()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'isPublished' => true
                ],
                [
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'isPublished' => true
                ],
            ])
        );

        file_put_contents(__DIR__.'/test/testing.md', '# Testing');
        file_put_contents(__DIR__.'/test/homepage.md', '# Homepage');

        $results = FileFinder::find(Article::instance(), 'testing');

        $this->assertCount(1, $results);
    }

    public function test_finder_only_returns_published_content()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d'),
                    'isPublished' => true
                ],
                [
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'isPublished' => false
                ],
            ])
        );

        file_put_contents(__DIR__.'/test/testing.md', '# Testing things');
        file_put_contents(__DIR__.'/test/homepage.md', '# Homepage things');

        $results = FileFinder::find(Article::instance(), 'things');

        $this->assertCount(1, $results);
    }
}
