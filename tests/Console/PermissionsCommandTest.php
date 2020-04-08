<?php


namespace AloiaCms\Tests\Console;

use AloiaCms\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class PermissionsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $content_path = __DIR__.'/test';

        if (!is_dir($content_path)) {
            mkdir($content_path, 0777, true);

            mkdir("{$content_path}/collections");
            mkdir("{$content_path}/images");
            mkdir("{$content_path}/images/pages");
            mkdir("{$content_path}/images/articles");
            mkdir("{$content_path}/files");
        }

        Config::set('aloiacms.collections.collections_path', "{$content_path}/collections");
        Config::set('aloiacms.pages.image_path', "{$content_path}/images/pages");
        Config::set('aloiacms.articles.image_path', "{$content_path}/images/articles");
        Config::set('aloiacms.uploaded_files.folder_path', "{$content_path}/files");
        Config::set('aloiacms.permissions.user', get_current_user());
        Config::set('aloiacms.permissions.group', get_current_user());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->recursively_remove_directory(__DIR__.'/test');
    }

    public function test_permissions_are_set_to_defined_user()
    {
        $this->artisan('aloiacms:set-permissions')
            ->assertExitCode(0);
    }
}
