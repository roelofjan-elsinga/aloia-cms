<?php

namespace FlatFileCms\Search;

use FlatFileCms\Article;
use FlatFileCms\Contracts\StorableInterface;
use Illuminate\Support\Collection;

class FileFinder
{

    /**@var string $folder_path*/
    private $folder_path;

    public function __construct(StorableInterface $storable)
    {
        $this->folder_path = $storable->getFolderPath();
    }

    /**
     * Search for the given text in this folder_path
     *
     * @param string $search_string
     * @return Collection|Article[]
     */
    public function find(string $search_string): Collection
    {
        try {

            exec("grep -iRl \"{$search_string}\" {$this->folder_path}", $files);

            return Collection::make($files)

                ->map(function(string $file_path) {

                    $filename_without_extension = pathinfo($file_path, PATHINFO_FILENAME);

                    return Article::forSlug($filename_without_extension);

                });

        } catch (\Exception $exception) {

            return new Collection();

        }
    }

}