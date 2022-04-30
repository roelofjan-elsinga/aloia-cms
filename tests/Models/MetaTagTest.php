<?php

namespace AloiaCms\Tests\Models;

use AloiaCms\Facades\MetaTagFacade;
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

    public function test_meta_tags_can_be_accessed_through_service_container()
    {
        MetaTag::find('testing')
            ->setExtension('html')
            ->setMatter([
                'title' => 'This is a title',
                'description' => 'This is a description',
                'author' => 'Post author',
                'image_url' => 'https://google.com/image.jpeg',
            ])
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", trim(app(MetaTag::class)->findById('testing')->body()));
    }

    public function test_meta_tags_can_be_accessed_through_facade()
    {
        MetaTag::find('testing')
            ->setExtension('html')
            ->setMatter([
                'title' => 'This is a title',
                'description' => 'This is a description',
                'author' => 'Post author',
                'image_url' => 'https://google.com/image.jpeg',
            ])
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", trim(MetaTagFacade::findById('testing')->body()));
    }
}
