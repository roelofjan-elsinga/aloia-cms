<?php

namespace FlatFileCms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @deprecated deprecated since version 1.0.0
 */
class TagsParser
{
    public static function instance(): TagsParser
    {
        return new TagsParser();
    }

    /**
     * Get the meta tags for the given page name
     *
     * @param string $name
     * @return \stdClass
     */
    public function getTagsForPageName(string $name): \stdClass
    {
        $all_tags = $this->getAllTags();

        $default_tags = $this->getDefaultTags();

        $filtered_array = array_filter($all_tags, function ($tags, $pageName) use ($name) {
            return $pageName === $name;
        }, 1);

        if (isset($filtered_array[$name])) {
            return $this->fillInBlankFieldsWithDefault($filtered_array[$name], $default_tags);
        } else {
            return $this->fillInBlankFieldsWithDefault(head($all_tags), $default_tags);
        }
    }

    /**
     * Get the default tags for any missing information
     *
     * @return \stdClass
     */
    private function getDefaultTags(): \stdClass
    {
        $parsed_config = $this->parseConfigFile();

        $default_tags = Collection::make($parsed_config['tags'])
            ->filter(function ($tags, $pageName) {
                return $pageName === 'default';
            })
            ->first();

        return $this->convertArrayToStdClass($default_tags);
    }

    /**
     * Fill in any missing fields with the default values, if they exist
     *
     * @param $tags
     * @param $default_tags
     * @return \stdClass
     */
    private function fillInBlankFieldsWithDefault($tags, $default_tags): \stdClass
    {
        foreach ($tags as $key => $value) {
            if ($this->shouldReplaceWithDefault($default_tags, $value, $key)) {
                $tags->$key = $default_tags->$key;
            }
        }

        foreach ($default_tags as $key => $value) {
            if (!isset($tags->$key)) {
                $tags->$key = $default_tags->$key;
            }
        }

        return $tags;
    }

    /**
     * @param $default_tags
     * @param $value
     * @param $key
     * @return bool
     */
    private function shouldReplaceWithDefault($default_tags, $value, $key): bool
    {
        return is_null($value) && isset($default_tags->$key);
    }

    /**
     * Get all meta tags in the config file
     *
     * @return array
     */
    public function getAllTags(): array
    {
        $parsed_config = $this->parseConfigFile();

        return Collection::make($parsed_config['tags'])
            ->mapWithKeys(function ($tags, $pageName) {
                return [$pageName => $this->convertArrayToStdClass($tags)];
            })
            ->toArray();
    }

    /**
     * Convert an array to a stdClass
     *
     * @param array $input_array
     * @return \stdClass
     */
    private function convertArrayToStdClass(array $input_array): \stdClass
    {
        $output_class = new \stdClass();

        foreach (array_keys($input_array) as $key) {
            $output_class->$key = $input_array[$key];
        }

        return $output_class;
    }

    /**
     * Parse the meta tags config file
     *
     * @return array
     */
    public function parseConfigFile(): array
    {
        $file_path = $this->getMetaTagsFilePath();

        $this->createConfigFileIfNotExists($file_path);

        return json_decode(file_get_contents($file_path), true);
    }

    /**
     * Get the file path of the meta tags configuration file
     *
     * @return string
     */
    private function getMetaTagsFilePath(): string
    {
        return Config::get('flatfilecms.meta_tags.file_path');
    }

    /**
     * In case the configuration file does not exists, it needs to be created to avoid errors.
     *
     * @param string $file_path
     */
    private function createConfigFileIfNotExists(string $file_path)
    {
        if (!file_exists($file_path)) {
            file_put_contents($file_path, json_encode([
                "tags" => [
                    "default" => [
                        "title" => "Page title",
                        "keywords" => "Keywords go here",
                        "description" => "Page description",
                        "author" => "Author name",
                        "image_small" => "",
                        "image_large" => ""
                    ]
                ]
            ], JSON_PRETTY_PRINT));
        }
    }
}
