<?php


namespace FlatFileCms\Tests\Search;

use FlatFileCms\Models\Article;
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

        Config::set('flatfilecms.collections_path', $content_path);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->recursively_remove_directory(__DIR__.'/test');
    }

    public function test_no_results_are_returned_when_no_matching_files_found()
    {
        $results = FileFinder::find(new Article, 'testing');

        $this->assertCount(0, $results);
    }

    public function test_only_files_with_matching_content_are_found_for_search_terms()
    {
        Article::find('testing')
            ->setExtension('md')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'isPublished' => true
            ])
            ->setBody('# Testing')
            ->save();

        Article::find('homepage')
            ->setExtension('md')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'isPublished' => true
            ])
            ->setBody('# Homepage')
            ->save();

        $results = FileFinder::find(new Article, 'testing');

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_published);
    }

    public function test_finder_only_returns_published_content()
    {
        Article::find('testing')
            ->setExtension('md')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'isPublished' => false
            ])
            ->setBody('# Testing things')
            ->save();

        Article::find('homepage')
            ->setExtension('md')
            ->setMatter([
                'post_date' => date('Y-m-d'),
                'isPublished' => true
            ])
            ->setBody('# Homepage things')
            ->save();

        $results = FileFinder::find(new Article, 'things');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('# Homepage things', $results->first()->rawBody());
    }
}
