<?php

namespace AloiaCms;

use AloiaCms\Contracts\StorableInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * @deprecated deprecated since version 1.0.0
 */
abstract class Content
{
    /**
     * Get the path to the meta data file
     *
     * @return string
     */
    abstract protected static function getMetaDataFilePath(): string;

    /**
     * Get the raw meta data without converting the data to an Article
     *
     * @return Collection
     */
    abstract public static function raw(): Collection;

    /**
     * Get all content resources
     *
     * @return Collection|StorableInterface[]
     */
    abstract public static function all(): Collection;

    /**
     * Update the meta data file with updated articles meta data
     *
     * @param Collection $articles
     */
    abstract public static function update(Collection $articles): void;

    /**
     * Get the parsed body of this article
     *
     * @return string
     * @throws \Exception
     */
    abstract public function content(): string;

    /**
     * Check if the meta data file exists and create it if it doesn't
     *
     * @param string $file_path
     * @return void
     */
    abstract protected static function validateMetaDataFile(string $file_path);

    /**
     * Generate a description for the content
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function getDescriptionFromContent(): string
    {
        $paragraphs = HtmlParser::getTextBetweenTags($this->content(), 'p');

        $paragraphs_with_text_content = array_filter($paragraphs, function ($paragraph) {
            return !empty(strip_tags($paragraph));
        });

        if (count($paragraphs_with_text_content) > 0) {
            return substr(head($paragraphs_with_text_content), 0, 160);
        }

        return "";
    }

    /**
     * Convert block tags in HTML strings into block content
     *
     * @param string $html_string
     * @return string
     */
    protected function parseContentBlocks(string $html_string): string
    {
        return (new InlineBlockParser)->parseHtmlString($html_string, true);
    }
}
