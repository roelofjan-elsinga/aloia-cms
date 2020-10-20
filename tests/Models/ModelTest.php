<?php

namespace AloiaCms\Tests\Models;

use AloiaCms\Models\Article;
use AloiaCms\Tests\TestCase;

class ModelTest extends TestCase
{
    public function test_matter_is_not_overwritten_when_using_add_matter()
    {
        $article = Article::find('testing')
            ->setMatter([
                'title' => 'title',
                'description' => 'description'
            ]);

        $this->assertSame('title', $article->matter()['title']);
        $this->assertSame('description', $article->matter()['description']);

        $article->set('title', 'New title');

        $this->assertSame('New title', $article->matter()['title']);
        $this->assertSame('description', $article->matter()['description']);
    }

    public function test_file_name_can_be_retrieved()
    {
        $article = Article::find('testing')
            ->setMatter([
                'title' => 'title',
                'description' => 'description'
            ]);

        $this->assertSame('testing', $article->filename());
    }

    public function test_value_can_be_set_on_model_instance()
    {
        $article = Article::find('testing')
            ->set('title', 'Article title');

        $this->assertSame('Article title', $article->get('title'));
    }
    
    public function test_non_specified_configuration_attributes_are_not_overwritten()
    {
        $article = Article::find('testing')
            ->setMatter([
                'title' => 'Article title',
                'description' => 'description'
            ]);

        $this->assertSame('Article title', $article->get('title'));
        $this->assertSame('description', $article->get('description'));

        $article->setMatter(['description' => 'Article description']);

        $this->assertSame('Article title', $article->get('title'));
        $this->assertSame('Article description', $article->get('description'));
    }
}
