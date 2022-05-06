<?php

namespace AloiaCms\Tests;

use AloiaCms\AloiaCmsServiceProvider;
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

        $invalid_permissions_folder = vfsStream::newDirectory("invalid_articles", 0600);

        $this->fs = vfsStream::setup('root', 0777, [
            'content' => [
                'collections' => [],
                'test-file.txt' => 'Test',
                'public' => []
            ],
            'images' => [
                'image.jpg' => 'image',
                'image.png' => 'image',
                'image_w300.png' => 'image',
            ],

        ]);

        $content_path = $this->fs->getChild('content')->url();

        Config::set('aloiacms.collections_path', "{$content_path}/collections");
        Config::set('aloiacms.seo.sitemap_path', "{$content_path}/public/sitemap.xml");
    }

    protected function getPackageProviders($app)
    {
        return [AloiaCmsServiceProvider::class];
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
