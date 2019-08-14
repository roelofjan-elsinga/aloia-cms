<?php

namespace FlatFileCms\Tests;

use FlatFileCms\FileManager;

class FileManagerTest extends TestCase
{

    public function test_folder_is_created_when_none_existent()
    {
        $this->assertFalse($this->fs->hasChild('content/files'));

        FileManager::all();

        $this->assertTrue($this->fs->hasChild('content/files'));
    }

//    public function test_files_can_be_retrieved_after_uploading()
//    {
//        $files = FileManager::all();
//
//        $this->assertSame(0, $files->count());
//
//        $file = vfsStream::newFile('test.txt')->at($this->fs)->setContent("The new contents of the file");
//
//        $uploaded_file = UploadedFile::createFromBase(new UploadedFile($file->url(), 'test-file.txt'), true);
//
//        FileManager::open()->upload($uploaded_file);
//
//        $files = FileManager::all();
//
//        $this->assertSame(1, $files->count());
//    }

//    public function test_file_is_deleted()
//    {
//
//    }

}