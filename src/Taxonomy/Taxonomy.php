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

    /**
     * Get the path of the file which contains the taxonomy information
     *
     * @return string
     */
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
        $taxonomies = Taxonomy::get()->toArray();

        self::nestedConditionalTask(
            $taxonomies,
            function(array $taxonomy) use ($child_category) {
                $taxonomy['children'][] = $child_category;

                return $taxonomy;
            },
            function(array $taxonomy) use ($parent_name) {
                return $taxonomy['category_name'] === $parent_name;
            }
        );

        self::update(new Collection($taxonomies));
    }

    /**
     * Update the taxonomy details for the taxonomy with the given name
     *
     * @param string $parent_url_prefix
     * @param array $parent_category
     */
    public static function updateCategoryWithUrlPrefix(string $parent_url_prefix, array $parent_category): void
    {
        $taxonomies = Taxonomy::get()->toArray();

        self::nestedConditionalTask(
            $taxonomies,
            function(array $taxonomy) use ($parent_category) {
                $taxonomy['category_name'] = $parent_category['category_name'];
                $taxonomy['category_url_prefix'] = $parent_category['category_url_prefix'];

                return $taxonomy;
            },
            function(array $taxonomy) use ($parent_url_prefix) {
                return $taxonomy['category_url_prefix'] === $parent_url_prefix;
            }
        );

        self::update(new Collection($taxonomies));
    }

    /**
     * Get a list of all the category names with children
     *
     * @return Collection
     */
    public static function list(): Collection
    {
        $taxonomies = Taxonomy::get()->toArray();

        self::nestedTask($taxonomies, function(array $taxonomy) {
            return [
                "category_name" => $taxonomy['category_name'],
                "children" => $taxonomy['children'],
            ];
        });

        return new Collection($taxonomies);
    }

    /**
     * Perform a task on all nested taxonomies
     *
     * @param array $taxonomies
     * @param callable $task
     */
    private static function nestedTask(array &$taxonomies, callable $task): void
    {

        foreach($taxonomies as $index => $taxonomy) {

            $taxonomy = $task($taxonomy);

            self::nestedTask($taxonomy['children'], $task);

            $taxonomies[$index] = $taxonomy;

        }

    }

    /**
     * Perform a task on all taxonomies if the condition passes validation
     *
     * @param array $taxonomies
     * @param callable $task
     * @param callable|null $condition
     */
    private static function nestedConditionalTask(array &$taxonomies, callable $task, ?callable $condition): void
    {

        foreach($taxonomies as $index => $taxonomy) {

            if($condition($taxonomy)) {

                $taxonomy = $task($taxonomy);

            } else {

                self::nestedConditionalTask($taxonomy['children'], $task, $condition);

            }

            $taxonomies[$index] = $taxonomy;

        }

    }

    /**
     * Remove the category with the given name from the taxonomy
     *
     * @param string $category_name
     */
    public static function destroy(string $category_name): void
    {
        $taxonomies = Taxonomy::get()->toArray();

        self::nestedTask(
            $taxonomies,
            function(array $taxonomy) use ($category_name) {

                $taxonomy['children'] = array_filter($taxonomy['children'], function($child) use ($category_name) {
                    return $child['category_name'] !== $category_name;
                });

                return $taxonomy;
            }
        );

        self::update(new Collection($taxonomies));
    }

}