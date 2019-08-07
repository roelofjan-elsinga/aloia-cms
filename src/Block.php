<?php

namespace FlatFileCms;


use ContentParser\ContentParser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
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
        return Config::get('flatfilecms.content_blocks.folder_path');
    }

    /**
     * Convert block tags in HTML strings into block content
     *
     * @param string $html_string
     * @return string
     */
    public function parseHtmlString(string $html_string): string
    {
        $tag_positions = $this->strpos_all($html_string, '===');

        if(count($tag_positions) % 2 !== 0) {
            return $html_string;
        }

        $pairs = [];

        foreach($tag_positions as $key => $position) {
            if($key % 2 === 0) {

                $pairs[] = [$position];

            } else {

                $last_index = count($pairs) - 1;

                $pairs[$last_index][] = $position;

            }
        }

        $replacements = [];

        foreach($pairs as $key => $pair) {

            $tag = substr($html_string, $pair[0], $pair[1] - $pair[0] + 3);

            $block_name = trim(str_replace("===", "", $tag));

            $replacements[$tag] = $this->getReplacementFromBlockName($tag, $block_name);

        }

        return str_replace(array_keys($replacements), array_values($replacements), $html_string);
    }

    /**
     * Get all occurrences of a needle in the haystack
     *
     * @param string $haystack
     * @param string $needle
     * @return array
     */
    function strpos_all(string $haystack, string $needle): array {

        $s = 0;
        $i = 0;

        while(is_integer($i)) {

            $i = stripos($haystack, $needle, $s);

            if(is_integer($i)) {
                $aStrPos[] = $i;
                $s = $i + strlen($needle);
            }
        }

        if(isset($aStrPos)) {
            return $aStrPos;
        } else {
            return [];
        }
    }

    /**
     * Parse the options given to the blocks and apply them as attributes to the surrounding div
     *
     * @param string $tag
     * @param string $block_name
     * @return string
     */
    private function getReplacementFromBlockName(string $tag, string $block_name): string
    {
        $index_of_options = strpos($block_name, '[');

        $options = '';

        if($index_of_options !== false) {

            $options_string = substr($block_name, $index_of_options + 1, -1);

            if(strlen($options_string) > 0) {
                $options = Collection::make(explode(',', $options_string))
                    ->reduce(function(string $carry, string $option) {

                        $option_pair = explode('=', $option);

                        return "{$carry}{$option_pair[0]}=\"{$option_pair[1]}\" ";

                    }, ' ');

                $options = rtrim($options);

                $block_name = substr($block_name, 0, $index_of_options);
            }

        }

        $block_value = $this->get($block_name);

        if(empty($block_value)) {
            $block_value = $tag;
        }

        return "<div{$options}>{$block_value}</div>";
    }

}