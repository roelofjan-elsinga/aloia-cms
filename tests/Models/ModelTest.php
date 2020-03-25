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

        $article->addMatter('title', 'New title');

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
}
