<?php

namespace FlatFileCms\Search;

use FlatFileCms\Article;
use FlatFileCms\Contracts\ArticleInterface;
use FlatFileCms\Contracts\StorableInterface;
use FlatFileCms\Page;
use Illuminate\Support\Collection;

class FileFinder
{

    /**
     * Search for the given text in this folder_path
     *
     * @param StorableInterface $storable
     * @param string $search_string
     * @return Collection|Article[]
     */
    public static function find(StorableInterface $storable, string $search_string): Collection
    {
        $instance_name = get_class($storable);

        $folder_path = $storable->getFolderPath();

        exec("grep -iRl \"{$search_string}\" {$folder_path}", $files);

        return Collection::make($files)

            ->map(function (string $file_path) use ($instance_name): ?ArticleInterface {
                $filename_without_extension = pathinfo($file_path, PATHINFO_FILENAME);

                return $instance_name::forSlug($filename_without_extension);
            })

            ->filter(function (?ArticleInterface $article) {
                return !is_null($article) && $article->isPublished();
            });
    }
}
