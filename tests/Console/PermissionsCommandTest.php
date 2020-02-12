<?php


namespace FlatFileCms\Tests\Console;

use FlatFileCms\Tests\TestCase;
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

            touch("{$content_path}/pages.json");
            touch("{$content_path}/articles.json");
            touch("{$content_path}/metatags.json");
            touch("{$content_path}/taxonomy.json");

            mkdir("{$content_path}/pages");
            mkdir("{$content_path}/articles");
            mkdir("{$content_path}/blocks");
            mkdir("{$content_path}/files");
        }

        Config::set('flatfilecms.pages.file_path', "{$content_path}/pages.json");
        Config::set('flatfilecms.pages.folder_path', "{$content_path}/pages");
        Config::set('flatfilecms.articles.file_path', "{$content_path}/articles.json");
        Config::set('flatfilecms.articles.folder_path', "{$content_path}/articles");
        Config::set('flatfilecms.content_blocks.folder_path', "{$content_path}/blocks");
        Config::set('flatfilecms.meta_tags.file_path', "{$content_path}/metatags.json");
        Config::set('flatfilecms.taxonomy.file_path', "{$content_path}/taxonomy.json");
        Config::set('flatfilecms.uploaded_files.folder_path', "{$content_path}/files");
        Config::set('flatfilecms.permissions.user', get_current_user());
        Config::set('flatfilecms.permissions.group', get_current_user());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->recursively_remove_directory(__DIR__.'/test');
    }

    public function test_permissions_are_set_to_defined_user()
    {
        $this->artisan('flatfilecms:set-permissions')
            ->assertExitCode(0);
    }
}
