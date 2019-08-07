<?php

namespace FlatFileCms\Tests\Console;

use FlatFileCms\Article;
use FlatFileCms\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

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

}