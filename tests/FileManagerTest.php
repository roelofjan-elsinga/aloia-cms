<?php

namespace FlatFileCms\Tests;

use FlatFileCms\FileManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use org\bovigo\vfs\vfsStream;

class FileManagerTest extends TestCase
{
    public function test_folder_is_created_when_none_existent()
    {
        $this->assertFalse($this->fs->hasChild('content/files'));

        FileManager::all();

        $this->assertTrue($this->fs->hasChild('content/files'));
    }

    public function test_files_can_be_retrieved_after_uploading()
    {
        Storage::fake('files');

        Config::set('flatfilecms.uploaded_files.folder_path', storage_path('framework/testing/disks/files'));

        $files = FileManager::all();

        $this->assertSame(0, $files->count());

        $file = UploadedFile::fake()->image('avatar.jpg');

        FileManager::open()->upload($file);

        $files = FileManager::all();

        $this->assertSame(1, $files->count());

        Storage::disk('files')->assertExists($file->getClientOriginalName());
    }

    public function test_file_is_deleted()
    {
        Storage::fake('files');

        Config::set('flatfilecms.uploaded_files.folder_path', storage_path('framework/testing/disks/files'));

        $files = FileManager::all();

        $this->assertSame(0, $files->count());

        $file = UploadedFile::fake()->image('avatar.jpg');

        FileManager::open()->upload($file);

        Storage::disk('files')->assertExists($file->getClientOriginalName());

        FileManager::delete('avatar.jpg');

        Storage::disk('files')->assertMissing($file->getClientOriginalName());
    }
}
