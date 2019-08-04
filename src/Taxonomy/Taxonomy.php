<?php


namespace FlatFileCms\Taxonomy;

use FlatFileCms\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Taxonomy
{
    /**
     * Get a TaxonomyCollection of TaxonomyLevel[]
     *
     * @return TaxonomyCollection
     */
    public static function get(): TaxonomyCollection
    {
        $file_path = self::getFilePath();

        self::validateFilePath($file_path);

        return TaxonomyCollection::make(json_decode(File::get($file_path), true))

            ->map(function(array $data_source) {

                return TaxonomyLevel::forDataSource($data_source);

            });
    }

    /**
     * Get the TaxonomyLevel by name
     *
     * @param string $category_name
     * @return TaxonomyLevel|null
     */
    public static function byName(string $category_name): ?TaxonomyLevel
    {
        return self::get()

            ->filter(function(TaxonomyLevel $level) use ($category_name) {

                return $level->name() === $category_name;

            })

            ->first();
    }

    /**
     * Get an empty, default state of the TaxonomyLevel
     *
     * @return TaxonomyLevel|null
     */
    public static function emptyState(): ? TaxonomyLevel
    {
        return TaxonomyLevel::forDataSource([
            'category_name' => 'home',
            'category_url_prefix' => '',
            'parent_category' => null
        ]);
    }

    /**
     * Get the TaxonomyLevel by URL
     *
     * @param string $category_url
     * @return TaxonomyLevel|null
     */
    public static function byUrl(string $category_url): ?TaxonomyLevel
    {
        return self::get()

            ->filter(function(TaxonomyLevel $level) use ($category_url) {

                return $level->url() === $category_url;

            })

            ->first();
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
                        "parent_category" => null,
                        "children" => []
                    ]
                ])
            );
        }
    }

    /**
     * Update the meta data file with updated articles meta data
     *
     * @param TaxonomyLevel[]|Collection $taxonomies
     */
    public static function update(Collection $taxonomies): void
    {
        $file_path = self::getFilePath();

        File::put(
            $file_path,
            $taxonomies
                ->map(function(TaxonomyLevel $level) {
                    return [
                        "category_url_prefix" => $level->url(),
                        "category_name" => $level->name(),
                        "parent_category" => $level->parent()
                    ];
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
        self::update(
            new Collection(
                Taxonomy::get()
                    ->add(
                        TaxonomyLevel::forDataSource(
                            array_merge($child_category, ['parent_category' => $parent_name])
                        )
                    )
            )
        );
    }

    /**
     * Update the taxonomy details for the taxonomy with the given name
     *
     * @param string $parent_url_prefix
     * @param array $parent_category
     */
    public static function updateCategoryWithUrlPrefix(string $parent_url_prefix, array $parent_category): void
    {
        $updating_taxonomy = Taxonomy::byUrl($parent_url_prefix);

        self::update(

            Taxonomy::get()

                ->map(function(TaxonomyLevel $level) use ($parent_url_prefix, $parent_category, $updating_taxonomy) {

                    if($level->url() === $parent_url_prefix) {

                        self::onUpdatingTaxonomy($level->name(), $parent_category['category_name']);

                        $level
                            ->setName($parent_category['category_name'])
                            ->setUrl($parent_category['category_url_prefix']);
                    }

                    // Update the children with the new parent category
                    else if($level->parent() === $updating_taxonomy->name()) {
                        $level->setParent($parent_category['category_name']);
                    }

                    return $level;

                })
        );
    }

    /**
     * Change the category on pages from the old name to the new name
     *
     * @param string $old_taxonomy_name
     * @param null|string $new_taxonomy_name
     */
    private static function onUpdatingTaxonomy(string $old_taxonomy_name, ?string $new_taxonomy_name): void
    {
        Page::update(

            Page::raw()

                ->map(function(array $page) use ($old_taxonomy_name, $new_taxonomy_name) {

                    if(isset($page['category']) && $page['category'] === $old_taxonomy_name) {
                        $page['category'] = $new_taxonomy_name;
                    }

                    return $page;

                })
        );
    }

    /**
     * Remove the category with the given name from the taxonomy
     *
     * @param string $category_name
     */
    public static function destroy(string $category_name): void
    {
        self::onUpdatingTaxonomy($category_name, null);

        self::update(
            Taxonomy::get()

                ->map(function(TaxonomyLevel $level) use ($category_name) {

                    // Update the children with the new parent category
                    if($level->parent() === $category_name) {
                        $level->setParent(null);
                    }

                    return $level;

                })

                ->filter(function(TaxonomyLevel $level) use ($category_name) {

                    return $level->name() !== $category_name;

                })

                ->values()
        );
    }

}