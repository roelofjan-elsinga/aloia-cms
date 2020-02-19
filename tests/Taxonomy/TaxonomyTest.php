<?php


namespace AloiaCms\Tests\Taxonomy;

use AloiaCms\Page;
use AloiaCms\Taxonomy\Taxonomy;
use AloiaCms\Tests\TestCase;
use Illuminate\Support\Collection;

class TaxonomyTest extends TestCase
{
    public function test_taxonomy_level_can_be_retrieved_by_url()
    {
        $taxonomy = Taxonomy::byUrl('');

        $this->assertSame('home', $taxonomy->name());
    }

    public function test_taxonomy_can_be_updated_with_url()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'static-pages',
            'category_url_prefix' => 'static-pages',
            'children' => []
        ]);

        $this->assertSame('static-pages', Taxonomy::byUrl('static-pages')->name());

        Taxonomy::updateCategoryWithUrlPrefix('static-pages', [
            'category_name' => 'pages',
            'category_url_prefix' => 'static-pages'
        ]);

        $this->assertSame('pages', Taxonomy::byUrl('static-pages')->name());
    }

    public function test_parent_taxonomy_can_be_updated_and_children_reflect_changes()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'static-pages',
            'category_url_prefix' => 'static-pages',
            'children' => []
        ]);

        $this->assertSame('home', Taxonomy::byUrl('static-pages')->parent());

        Taxonomy::updateCategoryWithUrlPrefix('', [
            'category_name' => 'home_page',
            'category_url_prefix' => ''
        ]);

        $this->assertSame('home_page', Taxonomy::byUrl('static-pages')->parent());
    }

    public function test_taxonomies_are_updated_on_pages_when_name_changes()
    {
        Page::update(
            Collection::make([
                [
                    'title' => 'Homepage title',
                    'description' => 'Homepage description',
                    'summary' => 'Homepage summary',
                    'template_name' => 'template',
                    'isPublished' => true,
                    'isScheduled' => false,
                    'filename' => 'homepage.md',
                    'postDate' => date('Y-m-d'),
                    'category' => 'home'
                ]
            ])
        );

        Taxonomy::updateCategoryWithUrlPrefix('', [
            'category_name' => 'home_page',
            'category_url_prefix' => ''
        ]);

        $this->assertSame('home_page', Page::forSlug('homepage')->taxonomy()->name());
    }

    public function test_taxonomy_can_be_removed()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'static-pages',
            'category_url_prefix' => 'static-pages',
            'children' => []
        ]);

        $this->assertNotNull(Taxonomy::byUrl('static-pages'));

        Taxonomy::destroy('static-pages');

        $this->assertNull(Taxonomy::byUrl('static-pages'));
    }

    public function test_child_taxonomies_are_removed_as_child_when_parent_is_deleted()
    {
        Taxonomy::addChildToCategoryWithName('home', [
            'category_name' => 'static-pages',
            'category_url_prefix' => 'static-pages',
            'children' => []
        ]);

        Taxonomy::addChildToCategoryWithName('static-pages', [
            'category_name' => 'pages',
            'category_url_prefix' => 'pages',
            'children' => []
        ]);

        $this->assertSame('static-pages', Taxonomy::byUrl('pages')->parent());

        Taxonomy::destroy('static-pages');

        $this->assertNull(Taxonomy::byUrl('pages')->parent());
    }
}
