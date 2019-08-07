<?php


namespace FlatFileCms\Tests;


use FlatFileCms\Block;

class BlockTest extends TestCase
{

    public function test_nothing_is_returned_if_block_does_not_exist()
    {
        $block = new Block();

        $this->assertEmpty($block->get('not_existing'));
    }

    public function test_html_is_returned_for_an_html_block()
    {
        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/testing.html", "<h1>Testing</h1>");

        $block = new Block();

        $this->assertSame("<h1>Testing</h1>", $block->get('testing'));
    }

    public function test_html_is_returned_for_a_markdown_block()
    {
        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/testing.md", "# Testing");

        $block = new Block();

        $this->assertSame("<h1>Testing</h1>", $block->get('testing'));
    }

    public function test_html_is_returned_for_a_txt_block()
    {
        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/testing.txt", "Testing");

        $block = new Block();

        $this->assertSame("Testing", $block->get('testing'));
    }

    public function test_block_is_ignored_in_template_if_it_does_not_exist()
    {
        $html_string = "This is a ===replacement=== for the string";

        $block = new Block();

        $this->assertSame("This is a ===replacement=== for the string", $block->parseHtmlString($html_string));
    }

    public function test_block_is_inserted_in_template()
    {
        $html_string = "This is a ===replacement=== for the string";

        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/replacement.html", "<strong>replacing text</strong>");

        $block = new Block();

        $this->assertSame("This is a <strong>replacing text</strong> for the string", $block->parseHtmlString($html_string));
    }

}