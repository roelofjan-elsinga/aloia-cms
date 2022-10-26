<?php

use AloiaCms\Models\Article;
use AloiaCms\Models\Model;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class Tutorial extends Model
{
}

test('matter is not overwritten when using addMatter()', function () {
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
});

test('filename can be retrieved', function () {
    $article = Article::find('testing')
        ->setMatter([
            'title' => 'title',
            'description' => 'description'
        ]);

    $this->assertSame('testing', $article->filename());
});

test('value can be set on model instance', function () {
    $article = Article::find('testing')
        ->set('title', 'Article title');

    $this->assertSame('Article title', $article->get('title'));
});

test('non-specified configuration attributes are not overwritten during update', function () {
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
});

test('data can be checked for existence', function () {
    $article = Article::find('testing')
        ->setMatter([
            'title' => 'Article title',
            'description' => 'description',
        ]);

    $this->assertTrue($article->has('title'));
    $this->assertFalse($article->has('summary'));

    $article->remove('title');

    $this->assertFalse($article->has('title'));
});

test('add matter still works after deprecation', function () {
    $article = Article::find('testing')
        ->set('title', 'Article title')
        ->addMatter('description', 'description');

    $this->assertSame('Article title', $article->get('title'));
    $this->assertSame('description', $article->get('description'));
});

test('model is routetable', function () {
    Route::get('/foo/{article}', function (Article $article) {
        return $article->matter();
    })->name('foo');

    $article = Article::find('testing')
        ->setMatter([
            'title' => 'Article title',
            'description' => 'description',
            'post_date' => now(),
        ])
        ->save();

    $this->assertSame('/foo/testing', URL::route('foo', [$article], false));
});

test('model can be implicitely bound to the route', function () {
    Route::get('/foo/{article}', function (Article $article) {
        return $article->matter();
    })
        ->name('foo')
        ->middleware(SubstituteBindings::class);

    $article = Article::find('testing')
        ->setMatter($attributes = [
            'title' => 'Article title',
            'description' => 'description',
            'post_date' => (string) now(),
        ])
        ->save();

    $this->get(URL::route('foo', [$article], false))
        ->assertJson($attributes);
});

it('throws an exception when model route binding is not found', function () {
    Route::get('/foo/{article}', function (Article $article) {
        return $article->matter();
    })
        ->name('foo')
        ->middleware(SubstituteBindings::class);

    $this->get('/foo/testing')->assertNotFound();
});

test('model without explicit folder name will be placed in a best-guess folder', function () {
    $tutorials_root_path = \Illuminate\Support\Facades\Config::get('aloiacms.collections_path') . '/tutorials';

    expect(file_exists($tutorials_root_path))->toBe(false);

    $tutorial = Tutorial::find('episode-1')->save();

    expect(file_exists($tutorials_root_path))->toBe(true);
    expect(file_exists($tutorials_root_path . '/episode-1.md'))->toBe(true);
});

test('attribute can be set on a model using the magic setter', function () {
    $tutorial = Tutorial::find('episode-1');

    $tutorial->title = 'Episode 1';

    expect($tutorial->matter())->toBe([
        'title' => 'Episode 1',
    ]);
});

test('body content can be set on a model using the magic setter', function () {
    $tutorial = Tutorial::find('episode-1');

    $tutorial->body = '# Test';

    expect($tutorial->rawBody())->toBe('# Test');
    expect($tutorial->body())->toBe('<h1>Test</h1>');
});
