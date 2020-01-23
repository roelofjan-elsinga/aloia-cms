<?php

namespace FlatFileCms\Tests;

use FlatFileCms\AppServiceProvider;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * @var  vfsStreamDirectory
     */
    protected $fs;

    public function setUp(): void
    {
        parent::setUp();

        $this->fs = vfsStream::setup('root', 0777, [
            'content' => [
                'pages' => [],
                'articles' => [],
                'blocks' => [],
                'collections' => [],
                'test-file.txt' => 'Test',
            ],
            'images' => [
                'image.jpg' => 'image',
                'image.png' => 'image',
                'image_w300.png' => 'image',
            ]
        ]);

        $content_path = $this->fs->getChild('content')->url();

        Config::set('flatfilecms.collections_path', "{$content_path}/collections");
        Config::set('flatfilecms.pages.file_path', "{$content_path}/pages.json");
        Config::set('flatfilecms.pages.folder_path', "{$content_path}/pages");
        Config::set('flatfilecms.articles.file_path', "{$content_path}/articles.json");
        Config::set('flatfilecms.articles.folder_path', "{$content_path}/articles");
        Config::set('flatfilecms.content_blocks.folder_path', "{$content_path}/blocks");
        Config::set('flatfilecms.meta_tags.file_path', "{$content_path}/metatags.json");
        Config::set('flatfilecms.taxonomy.file_path', "{$content_path}/taxonomy.json");
        Config::set('flatfilecms.uploaded_files.folder_path', "{$content_path}/files");
    }

    protected function getPackageProviders($app)
    {
        return [AppServiceProvider::class];
    }

    protected function getFileContentsFromFilePath(string $file_path): string
    {
        return file_get_contents($this->fs->getChild($file_path)->url());
    }

    protected function recursively_remove_directory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->recursively_remove_directory($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
