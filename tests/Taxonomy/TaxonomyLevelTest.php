<?php


namespace AloiaCms\Tests\Taxonomy;

use AloiaCms\Taxonomy\Taxonomy;
use AloiaCms\Taxonomy\TaxonomyLevel;
use AloiaCms\Tests\TestCase;

class TaxonomyLevelTest extends TestCase
{
    public function test_taxonomy_level_can_be_initialized_with_an_array()
    {
        $taxonomy = TaxonomyLevel::forDataSource([]);

        $this->assertSame(TaxonomyLevel::class, get_class($taxonomy));
    }

    public function test_a_name_can_be_set()
    {
        $taxonomy = TaxonomyLevel::forDataSource([]);

        $taxonomy->setName('category_name');

        $this->assertSame('category_name', $taxonomy->name());
    }

    public function test_url_returns_null_if_not_set()
    {
        $taxonomy = TaxonomyLevel::forDataSource([]);

        $this->assertNull($taxonomy->url());
    }

    public function test_url_can_be_set()
    {
        $taxonomy = TaxonomyLevel::forDataSource([]);

        $taxonomy->setUrl('pages');

        $this->assertSame('pages', $taxonomy->url());
    }

    public function test_full_url_can_be_retrieved()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'pages',
            'category_url_prefix' => 'pages'
        ]);

        Taxonomy::addChildToCategoryWithName('pages', [
            'category_name' => 'types',
            'category_url_prefix' => 'types'
        ]);

        $this->assertSame('pages/types', Taxonomy::byName('types')->fullUrl());
    }

    public function test_parent_category_can_be_retrieved()
    {
        $taxonomy = TaxonomyLevel::forDataSource([]);

        $taxonomy->setParent('home');

        $this->assertSame('home', $taxonomy->parent());
    }

    public function test_taxonomy_can_be_retrieved_as_nested_list()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'pages',
            'category_url_prefix' => 'pages'
        ]);

        Taxonomy::addChildToCategoryWithName('pages', [
            'category_name' => 'types',
            'category_url_prefix' => 'types'
        ]);

        $tree = Taxonomy::get()->asNestedList();

        $this->assertSame('pages', $tree->first()->children()->first()->name());
    }

    public function test_children_can_be_set()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'pages',
            'category_url_prefix' => 'pages'
        ]);

        Taxonomy::addChildToCategoryWithName('pages', [
            'category_name' => 'types',
            'category_url_prefix' => 'types'
        ]);

        $tree = Taxonomy::byName('home');

        $tree->addChild(
            Taxonomy::byName('types')
        );

        $this->assertCount(1, $tree->children());

        $tree->setChildren(
            Taxonomy::get()
                ->filter(function (TaxonomyLevel $level) {
                    return $level->name() !== 'home';
                })
        );

        $this->assertCount(2, $tree->children());
    }

    public function test_children_can_be_added()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'pages',
            'category_url_prefix' => 'pages'
        ]);

        Taxonomy::addChildToCategoryWithName('pages', [
            'category_name' => 'types',
            'category_url_prefix' => 'types'
        ]);

        $tree = Taxonomy::byName('home');

        $tree->addChildren(
            Taxonomy::get()
                ->filter(function (TaxonomyLevel $level) {
                    return $level->name() !== 'home';
                })
        );

        $this->assertCount(2, $tree->children());
    }
}
