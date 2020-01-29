<?php

namespace FlatFileCms\Search;

use FlatFileCms\Models\Article;
use FlatFileCms\Models\Contracts\PublishInterface;
use FlatFileCms\Models\Page;
use FlatFileCms\Contracts\StorableInterface;
use Illuminate\Support\Collection;

class FileFinder
{

    /**
     * Search for the given text in this folder_path
     *
     * @param StorableInterface $storable
     * @param string $search_string
     * @return Collection|Article[]|Page[]
     */
    public static function find(StorableInterface $storable, string $search_string): Collection
    {
        $instance_name = get_class($storable);

        $folder_path = $storable->getFolderPath();

        exec("grep -iRl \"{$search_string}\" {$folder_path}", $files);

        return Collection::make($files)

            ->map(function (string $file_path) use ($instance_name): ?PublishInterface {
                $filename_without_extension = pathinfo($file_path, PATHINFO_FILENAME);

                return $instance_name::find($filename_without_extension);
            })

            ->filter(function (?PublishInterface $model): bool {
                return !is_null($model) && $model->isPublished();
            });
    }
}
