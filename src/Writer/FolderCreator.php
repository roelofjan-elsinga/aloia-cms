<?php


namespace AloiaCms\Writer;

class FolderCreator
{
    /**
     * Create a folder for the given path if it doesn't exist
     *
     * @param string $folder_path
     */
    public static function forPath(string $folder_path): void
    {
        if (!file_exists($folder_path)) {
            mkdir($folder_path, 0777, true);
        }
    }
}
