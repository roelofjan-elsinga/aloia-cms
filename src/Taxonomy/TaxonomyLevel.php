<?php


namespace FlatFileCms\Taxonomy;


use Illuminate\Support\Collection;

class TaxonomyLevel
{
    /**
     * @var array
     */
    private $data_source;

    /**
     *
     *
     * TaxonomyLevel constructor.
     * @param array $data_source
     */
    private function __construct(array $data_source)
    {
        $this->data_source = $data_source;
    }

    /**
     * Generate an instance for the given data source
     *
     * @param array $data_source
     * @return TaxonomyLevel
     */
    public static function forDataSource(array $data_source): TaxonomyLevel
    {
        return new static($data_source);
    }

    /**
     * Get the name of this category
     *
     * @return null|string
     */
    public function name(): ?string
    {
        return $this->data_source['category_name'] ?? null;
    }

    /**
     * Set the name of this level
     *
     * @param string $name
     * @return TaxonomyLevel
     */
    public function setName(string $name): TaxonomyLevel
    {
        $this->data_source['category_name'] = $name;

        return $this;
    }

    /**
     * Get the URL segment of this category
     *
     * @return null|string
     */
    public function url(): ?string
    {
        return $this->data_source['category_url_prefix'] ?? null;
    }

    /**
     * Get the full URL segment (including parents) of this category
     *
     * @return null|string
     */
    public function fullUrl(): string
    {
        $parents = new Collection();

        $current_level = Taxonomy::byName($this->name());

        $parents->add($current_level);

        $parent = $current_level->parent();

        while(!is_null($parent)) {

            $current_level = Taxonomy::byName($parent);

            $parents->add($current_level);

            $parent = $current_level->parent();

        }

        return $parents
            ->filter(function(TaxonomyLevel $level) {
                return !empty($level->url());
            })
            ->reverse()
            ->map(function(TaxonomyLevel $level) {
                return $level->url();
            })
            ->implode('/');
    }

    /**
     * Set the url of this level
     *
     * @param string $url
     * @return TaxonomyLevel
     */
    public function setUrl(string $url): TaxonomyLevel
    {
        $this->data_source['category_url_prefix'] = $url;

        return $this;
    }

    /**
     * Get the name of the parent category
     *
     * @return null|string
     */
    public function parent(): ?string
    {
        return $this->data_source['parent_category'] ?? null;
    }

    /**
     * Set the parent of this level
     *
     * @param string|null $name
     * @return TaxonomyLevel
     */
    public function setParent(?string $name): TaxonomyLevel
    {
        $this->data_source['parent_category'] = $name;

        return $this;
    }

    /**
     * Get the children of this taxonomy level
     *
     * @return Collection
     */
    public function children(): Collection
    {
        return $this->data_source['children'] ?? new Collection();
    }

    /**
     * Replace the children in the collection
     *
     * @param Collection $children
     * @return TaxonomyLevel
     */
    public function setChildren(Collection $children): TaxonomyLevel
    {
        $this->data_source['children'] = $children;

        return $this;
    }

    /**
     * Add a child to the collection
     *
     * @param TaxonomyLevel $level
     * @return TaxonomyLevel
     */
    public function addChild(TaxonomyLevel $level): TaxonomyLevel
    {
        $children = $this->children()->add($level);

        $this->data_source['children'] = $children;

        return $this;
    }

    /**
     * @param TaxonomyLevel[]|Collection $levels
     * @return TaxonomyLevel
     */
    public function addChildren(Collection $levels): TaxonomyLevel
    {
        foreach($levels as $level) {
            $this->addChild($level);
        }

        return $this;
    }
}