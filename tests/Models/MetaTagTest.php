<?php

namespace AloiaCms\Tests\Models;

use AloiaCms\Models\MetaTag;
use AloiaCms\Tests\TestCase;

class MetaTagTest extends TestCase
{
    public function test_tags_can_be_parsed_by_page_name()
    {
        $tags = MetaTag::find('default')
            ->setMatter([
                'title' => 'This is a title',
                'description' => 'This is a description',
                'author' => 'Post author',
                'image_url' => 'https://google.com/image.jpeg',
            ])
            ->save();

        $this->assertSame('This is a title', $tags->title);
        $this->assertSame('This is a description', $tags->description);
        $this->assertSame('Post author', $tags->author);
        $this->assertSame('https://google.com/image.jpeg', $tags->image_url);
    }

    public function test_tags_are_null_when_content_does_not_exist()
    {
        $tags = MetaTag::find('default');

        $this->assertNull($tags->title);
        $this->assertNull($tags->description);
        $this->assertNull($tags->author);
        $this->assertNull($tags->image_url);
    }
}
