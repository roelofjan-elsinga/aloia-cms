<?php


namespace FlatFileCms\Tests;

use FlatFileCms\HtmlParser;

class HtmlParserTest extends TestCase
{
    public function test_tag_attribute_can_be_found()
    {
        $html_string = "<div><img src='https://google.com'/><img src='https://yahoo.com'/></div>";

        $links = HtmlParser::getTagAttribute($html_string, 'img', 'src');

        $this->assertSame(['https://google.com', 'https://yahoo.com'], $links);
    }
}
