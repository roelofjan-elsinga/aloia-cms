<?php

namespace FlatFileCms\Tests\Console;

use FlatFileCms\Article;
use FlatFileCms\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use org\bovigo\vfs\vfsStream;

class GenerateArticleFilesTest extends TestCase
{
    public function test_article_file_is_not_created_if_no_data_file_present()
    {
        $this->artisan('flatfilecms:article:generate');

        $this->assertCount(0, File::allFiles($this->fs->getChild('content/articles')->url()));
    }

    public function test_article_file_is_created_from_data_file()
    {
        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->artisan('flatfilecms:article:generate')
            ->expectsOutput("Created testing.md");

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
    }

    public function test_existing_article_file_generation_is_skipped()
    {
        file_put_contents(vfsStream::url('root/content/articles/testing.md'), '# Testing');

        Article::update(
            Collection::make([
                [
                    'filename' => 'testing.md',
                    'postDate' => date('Y-m-d')
                ]
            ])
        );

        $this->artisan('flatfilecms:article:generate')
            ->assertExitCode(0);

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
    }
}
