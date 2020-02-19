<?php


namespace AloiaCms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class FileManager
{

    /**@var string $uploaded_files_folder_path*/
    private $uploaded_files_folder_path;

    /**
     * FileManager constructor.
     */
    private function __construct()
    {
        $this->uploaded_files_folder_path = Config::get('aloiacms.uploaded_files.folder_path');

        $this->assertFolderPathExists();
    }

    /**
     * Open a new FileManager instance
     *
     * @return FileManager
     */
    public static function open(): FileManager
    {
        return new static();
    }

    /**
     * Move the UploadedFile to the chosen destination folder
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function upload(UploadedFile $file): bool
    {
        $file->move($this->uploaded_files_folder_path, $file->getClientOriginalName());

        return true;
    }

    /**
     * Get all uploaded files in the destination folder
     *
     * @return Collection
     */
    public function get(): Collection
    {
        return Collection::make(File::allFiles($this->uploaded_files_folder_path))
            ->map(function (string $file_path) {
                return \AloiaCms\DataSource\File::forFilePath($file_path);
            });
    }

    /**
     * Get all uploaded files in the destination folder
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        return self::open()->get();
    }

    /**
     * Delete the file for the given file name
     *
     * @param string $file_name
     */
    public function deleteForFilename(string $file_name): void
    {
        $file_path = "{$this->uploaded_files_folder_path}/{$file_name}";

        File::delete($file_path);
    }

    /**
     * Delete the file for the given file name
     *
     * @param string $file_name
     */
    public static function delete(string $file_name): void
    {
        self::open()->deleteForFilename($file_name);
    }

    /**
     * Assert the upload folder exists, otherwise create it
     */
    private function assertFolderPathExists()
    {
        if (!is_dir($this->uploaded_files_folder_path)) {
            mkdir($this->uploaded_files_folder_path);
        }
    }
}
