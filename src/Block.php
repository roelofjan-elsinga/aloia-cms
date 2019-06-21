<?php

namespace FlatFileCms;


use ContentParser\ContentParser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Block
{

    /**
     * Get a content block by name and parse it, or return an empty string.
     *
     * @param string $block_name
     * @return string
     */
    public function get(string $block_name): string
    {
        try {

            $file_path = $this->getFilePathFromName($block_name);

            $parsed_content = ContentParser::forFile($file_path)->parse();

            return $parsed_content;

        } catch(\Exception $exception) {

            Log::error($exception->getMessage());

            return "";

        }
    }

    /**
     * Get the file path of the content file for the given name, throw exception if not found.
     *
     * @param string $block_name
     * @return string
     * @throws FileNotFoundException
     */
    private function getFilePathFromName(string $block_name): string
    {
        $folder_path = $this->getBlockFolderPath();

        $files_in_folder = File::allFiles($folder_path);

        $requested_files = array_values(array_filter($files_in_folder, function(\SplFileInfo $file) use ($block_name) {

            $filename = str_replace(".{$file->getExtension()}", "", $file->getFilename());

            return $filename === $block_name;
        }));

        if(count($requested_files) === 0) {
            throw new FileNotFoundException("Content block with name {$block_name} was not found at {$folder_path}");
        }

        return "{$folder_path}/{$requested_files[0]->getFilename()}";
    }

    /**
     * Get the folder path of the content blocks.
     *
     * @return string
     */
    private function getBlockFolderPath(): string
    {
        return Config::get('flatfilecms.content_blocks.data_path');
    }


}