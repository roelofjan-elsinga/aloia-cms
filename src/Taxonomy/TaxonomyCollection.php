<?php


namespace FlatFileCms\Taxonomy;

use Illuminate\Support\Collection;

class TaxonomyCollection extends Collection
{
    public function asNestedList()
    {
        $urls = [];

        foreach ($this->items as $item) {
            $parents = new Collection();

            $current_level = Taxonomy::byName($item->name());

            $parents->add($current_level);

            $parent = $current_level->parent();

            while (!is_null($parent)) {
                $current_level = Taxonomy::byName($parent);

                $parents->add($current_level);

                $parent = $current_level->parent();
            }

            $urls[] = $parents;
        }

        $urls = Collection::make($urls)
            ->sortByDesc(function (Collection $parents) {
                return $parents->count();
            })
            ->map(function (Collection $parents) {
                return $parents->first();
            });

        foreach ($urls as $url) {
            $self = $this->filter(function (TaxonomyLevel $level) use ($url) {
                return $level->name() === $url->name();
            })->first();

            $parent = $this->filter(function (TaxonomyLevel $level) use ($url) {
                return $level->name() === $url->parent();
            })->first();

            if (!is_null($parent)) {
                $parent->addChild($self);

                $this->items = $this
                    ->map(function (TaxonomyLevel $level) use ($parent) {
                        if ($level->name() === $parent->name()) {
                            $level = $parent;
                        }

                        return $level;
                    })
                    ->filter(function (TaxonomyLevel $level) use ($url) {
                        return $level->name() !== $url->name();
                    })
                    ->toArray();
            }
        }

        return TaxonomyCollection::make($this->items);
    }
}
