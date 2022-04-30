<?php

namespace AloiaCms\Tests\Models;

use AloiaCms\Facades\BlockFacade;
use AloiaCms\Models\ContentBlock;
use AloiaCms\Tests\TestCase;

class ContentBlockTest extends TestCase
{
    public function test_nothing_is_returned_if_block_does_not_exist()
    {
        $block = new ContentBlock();

        $this->assertEmpty($block->get('non_existent'));
    }

    public function test_html_is_returned_for_an_html_block()
    {
        ContentBlock::find('testing')
            ->setExtension('html')
            ->setBody("<h1>Testing</h1>")
            ->save();

        $block = new ContentBlock();

        $this->assertSame("<h1>Testing</h1>", $block->get('testing'));
    }

    public function test_html_is_returned_for_a_markdown_block()
    {
        ContentBlock::find('testing')
            ->setExtension('md')
            ->setBody("# Testing")
            ->save();

        $block = new ContentBlock();

        $this->assertSame("<h1>Testing</h1>", $block->get('testing'));
    }

    public function test_html_is_returned_for_a_txt_block()
    {
        ContentBlock::find('testing')
            ->setExtension('txt')
            ->setBody("Testing")
            ->save();

        $block = new ContentBlock();

        $this->assertSame("Testing", $block->get('testing'));
    }

    public function test_block_can_be_accessed_through_service_container()
    {
        ContentBlock::find('testing')
            ->setExtension('html')
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", app(ContentBlock::class)->get('testing'));
    }

    public function test_block_can_be_accessed_through_facade()
    {
        ContentBlock::find('testing')
            ->setExtension('html')
            ->setBody("<h1>Testing</h1>")
            ->save();

        $this->assertSame("<h1>Testing</h1>", BlockFacade::get('testing'));
    }
}
