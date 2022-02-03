<?php


namespace AloiaCms\Writer;

class FolderCreator
{
    /**
     * Create a folder for the given path if it doesn't exist
     *
     * @param string $folder_path
     * @throws \Exception
     */
    public static function forPath(string $folder_path): void
    {
        if (!file_exists($folder_path)) {
            if (!mkdir($folder_path, 0777, true)) {
                throw new \Exception("Failed to create folder for path {$folder_path}");
            }
        }
    }
}
