<?php


namespace FlatFileCms\Tests;

use FlatFileCms\InlineBlockParser;
use FlatFileCms\Facades\BlockFacade;
use FlatFileCms\Models\ContentBlock;

class InlineBlockParserTest extends TestCase
{
    public function test_nothing_is_returned_if_block_does_not_exist()
    {
        $this->assertEmpty(BlockFacade::get('not_existing'));
    }

    public function test_replacement_tags_are_preserved_when_no_closing_tags()
    {
        $html_string = "This is a ===replacement for the string";

        $block = new InlineBlockParser();

        $this->assertSame(
            "This is a ===replacement for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_is_ignored_in_template_if_it_does_not_exist()
    {
        $html_string = "This is a ===replacement=== for the string";

        $block = new InlineBlockParser();

        $this->assertSame("This is a <div>===replacement===</div> for the string", $block->parseHtmlString($html_string));
    }

    public function test_block_is_inserted_in_template()
    {
        $html_string = "This is a ===replacement=== for the string";

        ContentBlock::find('replacement')
            ->setExtension('html')
            ->setBody('<strong>replacing text</strong>')
            ->save();

        $block = new InlineBlockParser();

        $this->assertSame(
            "This is a <div><strong>replacing text</strong></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_is_inserted_including_options()
    {
        $html_string = "This is a ===replacement[class=big small,style=color:red;]=== for the string";

        ContentBlock::find('replacement')
            ->setExtension('html')
            ->setBody('<strong>replacing text</strong>')
            ->save();

        $block = new InlineBlockParser();

        $this->assertSame(
            "This is a <div class=\"big small\" style=\"color:red;\"><strong>replacing text</strong></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }

    public function test_block_gets_link_when_href_is_included()
    {
        $html_string = "This is a ===replacement[class=big small,href=/link-to-page]=== for the string";

        ContentBlock::find('replacement')
            ->setExtension('html')
            ->setBody('<strong>replacing text</strong>')
            ->save();

        $block = new InlineBlockParser();

        $this->assertSame(
            "This is a <div class=\"big small\"><a href=\"/link-to-page\"><strong>replacing text</strong></a></div> for the string",
            $block->parseHtmlString($html_string)
        );
    }
}
