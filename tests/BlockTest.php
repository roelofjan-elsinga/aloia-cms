<?php


namespace FlatFileCms\Tests;

use FlatFileCms\Block;
use FlatFileCms\Facades\BlockFacade;

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

    public function test_replacement_tags_are_preserved_when_no_closing_tags()
    {
        $html_string = "This is a ===replacement for the string";

        $block = new Block();

        $this->assertSame(
            "This is a ===replacement for the string",
            $block->parseHtmlString($html_string)
        );
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

        $this->assertSame("This is a <div>===replacement===</div> for the string", $block->parseHtmlString($html_string));
    }

    public function test_block_is_inserted_in_template()
    {
        $html_string = "This is a ===replacement=== for the string";

        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/replacement.html", "<strong>replacing text</strong>");

        $block = new Block();

        $this->assertSame(
            "This is a <div><strong>replacing text</strong></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_is_inserted_including_options()
    {
        $html_string = "This is a ===replacement[class=big small,style=color:red;]=== for the string";

        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/replacement.html", "<strong>replacing text</strong>");

        $block = new Block();

        $this->assertSame(
            "This is a <div class=\"big small\" style=\"color:red;\"><strong>replacing text</strong></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_gets_link_when_href_is_included()
    {
        $html_string = "This is a ===replacement[class=big small,href=/link-to-page]=== for the string";

        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/replacement.html", "<strong>replacing text</strong>");

        $block = new Block();

        $this->assertSame(
            "This is a <div class=\"big small\"><a href=\"/link-to-page\"><strong>replacing text</strong></a></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_can_be_accessed_through_service_container()
    {
        file_put_contents("{$this->fs->getChild('content/blocks')->url()}/testing.html", "<h1>Testing</h1>");

        $this->assertSame("<h1>Testing</h1>", app('FlatFileCmsBlock')->get('testing'));
        $this->assertSame("<h1>Testing</h1>", BlockFacade::get('testing'));
    }
}
