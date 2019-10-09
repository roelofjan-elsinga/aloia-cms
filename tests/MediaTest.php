<?php


namespace FlatFileCms\Tests;

use FlatFileCms\Media;
use Illuminate\Support\Facades\Config;

class MediaTest extends TestCase
{
    public function test_image_path_will_be_created_if_none_exists()
    {
        Config::set('flatfilecms.articles.image_path', $this->fs->getChild('content')->url() . '/images');

        $files = (new Media())->allFiles();

        $this->assertTrue($this->fs->hasChild('content/images'));
        $this->assertCount(0, $files);
    }

    public function test_files_can_be_retrieved()
    {
        Config::set('flatfilecms.articles.image_path', $this->fs->getChild('images')->url());

        $files = (new Media())->allFiles();

        $this->assertCount(3, $files);
    }

    public function test_filename_can_be_converted_to_title()
    {
        $title = Media::filenameToTitle('great_photo_of_cats.jpg');

        $this->assertSame('Great photo of cats', $title);
    }

    public function test_title_can_be_converted_to_filename()
    {
        $title = Media::titleToFilename('Great photo of cats');

        $this->assertSame('great-photo-of-cats', $title);
    }

    public function test_only_full_size_images_can_be_retrieved_through_collection()
    {
        Config::set('flatfilecms.articles.image_path', $this->fs->getChild('images')->url());

        $files = (new Media())->allFiles()->onlyFullSize();

        $this->assertCount(2, $files);
    }
}
