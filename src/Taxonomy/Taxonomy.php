<?php


namespace FlatFileCms\Taxonomy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Taxonomy
{

    public static function get(): Collection
    {
        $file_path = self::getFilePath();

        self::validateFilePath($file_path);

        // todo this needs to be converted to nested Taxonomy[]

        return Collection::make(json_decode(File::get($file_path), true));

    }

    public function parent(): ?string
    {
        // return the name of the parent category, or null of this is home.
    }

    public function children(): Collection
    {
        return Collection::make(/*$this->attributes['children']*/);
    }

    /**
     * Check if the meta data file exists and create it if it doesn't
     *
     * @param string $file_path
     * @return void
     */
    protected static function validateFilePath(string $file_path)
    {
        if(! File::exists($file_path)) {
            self::update(
                new Collection([
                    [
                        "category_url_prefix" => "",
                        "category_name" => "home",
                        "children" => []
                    ]
                ])
            );
        }
    }

    /**
     * Update the meta data file with updated articles meta data
     *
     * @param Collection $articles
     */
    public static function update(Collection $articles): void
    {
        $file_path = self::getFilePath();

        File::put(
            $file_path,
            $articles
                ->map(function($article) {
                    return \FlatFileCms\DataSource\Taxonomy::create($article)->toArray();
                })
                ->toJson(JSON_PRETTY_PRINT)
        );
    }

    private static function getFilePath(): string
    {
        return Config::get('flatfilecms.taxonomy.file_path');
    }

    /**
     * Add a child category to its parent
     *
     * @param string $parent_name
     * @param array $child_category
     */
    public static function addChildToCategoryWithName(string $parent_name, array $child_category): void
    {
        $taxonomy = Taxonomy::get()->toArray();

        self::insert($taxonomy, $parent_name, $child_category);

        self::update(new Collection($taxonomy));
    }

    /**
     * Append a child category into the parent category
     *
     * @param array $taxonomies
     * @param string $parent_name
     * @param array $child_category
     */
    private static function insert(array &$taxonomies, string $parent_name, array $child_category): void
    {
        foreach($taxonomies as $index => $taxonomy) {

            if($taxonomy['category_name'] === $parent_name) {

                $taxonomy['children'][] = $child_category;

                $taxonomies[$index] = $taxonomy;

                break;

            } else {

                self::insert($taxonomy['children'], $parent_name, $child_category);

            }

        }
    }

    /**
     * Update the taxonomy details for the taxonomy with the given name
     *
     * @param string $parent_url_prefix
     * @param array $parent_category
     */
    public static function updateCategoryWithUrlPrefix(string $parent_url_prefix, array $parent_category): void
    {
        $taxonomy = Taxonomy::get()->toArray();

        self::updateParent($taxonomy, $parent_url_prefix, $parent_category);

        self::update(new Collection($taxonomy));
    }

    /**
     * Update the taxonomy details for the given parent name
     *
     * @param array $taxonomies
     * @param string $parent_url_prefix
     * @param array $parent_category
     */
    private static function updateParent(array &$taxonomies, string $parent_url_prefix, array $parent_category): void
    {
        foreach($taxonomies as $index => $taxonomy) {

            if($taxonomy['category_url_prefix'] === $parent_url_prefix) {

                $taxonomies[$index]['category_name'] = $parent_category['category_name'];
                $taxonomies[$index]['category_url_prefix'] = $parent_category['category_url_prefix'];

                break;

            } else {

                self::updateParent($taxonomy['children'], $parent_url_prefix, $parent_category);

                $taxonomies[$index]['children'] = $taxonomy['children'];

            }

        }
    }
}