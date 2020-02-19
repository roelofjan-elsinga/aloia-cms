<?php


namespace AloiaCms\Tests;

use AloiaCms\TagsParser;
use Illuminate\Support\Facades\Config;

class TagsParserTest extends TestCase
{
    public function test_tags_can_be_parsed_by_page_name()
    {
        $tags = TagsParser::instance()->getTagsForPageName('default');

        $this->assertSame('Page title', $tags->title);
        $this->assertSame('Keywords go here', $tags->keywords);
        $this->assertSame('Page description', $tags->description);
        $this->assertSame('Author name', $tags->author);
        $this->assertSame('', $tags->image_small);
        $this->assertSame('', $tags->image_large);
    }

    public function test_tags_are_filled_with_default_values_if_page_has_no_tags()
    {
        $tags = TagsParser::instance()->getTagsForPageName('home');

        $this->assertSame('Page title', $tags->title);
        $this->assertSame('Keywords go here', $tags->keywords);
        $this->assertSame('Page description', $tags->description);
        $this->assertSame('Author name', $tags->author);
        $this->assertSame('', $tags->image_small);
        $this->assertSame('', $tags->image_large);
    }

    public function test_tags_are_filled_with_default_values_if_page_has_incomplete_tags()
    {
        $config_file = TagsParser::instance()->parseConfigFile();

        $config_file['tags']['home']['title'] = "Homepage";
        $config_file['tags']['home']['keywords'] = null;

        file_put_contents(Config::get('aloiacms.meta_tags.file_path'), json_encode($config_file));

        $tags = TagsParser::instance()->getTagsForPageName('home');

        $this->assertSame('Homepage', $tags->title);
        $this->assertSame('Keywords go here', $tags->keywords);
        $this->assertSame('Page description', $tags->description);
        $this->assertSame('Author name', $tags->author);
        $this->assertSame('', $tags->image_small);
        $this->assertSame('', $tags->image_large);
    }
}
