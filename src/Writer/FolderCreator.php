<?php


namespace FlatFileCms\Writer;

class FolderCreator
{
    public static function forPath(string $folder_path): void
    {
        if (!file_exists($folder_path)) {
            mkdir($folder_path);
        }
    }
}
