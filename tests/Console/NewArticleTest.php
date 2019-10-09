<?php


namespace FlatFileCms\Tests\Console;

use FlatFileCms\Tests\TestCase;

class NewArticleTest extends TestCase
{
    public function test_new_article_cannot_be_created_without_slug()
    {
        $this->expectExceptionMessage('You need to submit a slug using --slug');

        $this->artisan('flatfilecms:new:article');
    }

    public function test_new_article_can_be_created_with_slug()
    {
        $this->artisan('flatfilecms:new:article --slug=testing')
            ->expectsOutput('Created new post entry for testing');

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
    }

    public function test_post_date_can_be_defined_from_command_line()
    {
        $this->artisan('flatfilecms:new:article --slug=testing --post_date=2017-01-01')
            ->expectsOutput('Created new post entry for testing');

        $this->assertTrue($this->fs->hasChild('content/articles/testing.md'));
    }

    public function test_error_message_is_shown_when_providing_malformed_date_format()
    {
        $this->expectExceptionMessage('You need to submit the date with the following format: Y-m-d');

        $this->artisan('flatfilecms:new:article --slug=testing --post_date="2017-01-01 12:00:00"');

        $this->assertFalse($this->fs->hasChild('content/articles/testing.md'));
    }
}
