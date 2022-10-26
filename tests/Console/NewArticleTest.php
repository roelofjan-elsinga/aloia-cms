<?php

use AloiaCms\Models\Article;

test('new article cannot be created without a slug', function () {
    $this->expectExceptionMessage('You need to submit a slug using --slug');

    $this->artisan('aloiacms:new:article');
});

test('new article can be created with slug', function () {
    $this->artisan('aloiacms:new:article --slug=testing')
        ->expectsOutput('Created new post entry for testing');

    $this->assertTrue(Article::find('testing')->exists());
});

test('post date can be set using the command', function () {
    $this->artisan('aloiacms:new:article --slug=testing --post_date=2017-01-01')
        ->expectsOutput('Created new post entry for testing');

    $this->assertTrue(Article::find('testing')->exists());
});

test('error message is shown when incorrect date format is provided to command', function () {
    $this->expectExceptionMessage('You need to submit the date with the following format: Y-m-d');

    $this->artisan('aloiacms:new:article --slug=testing --post_date="2017-01-01 12:00:00"');

    $this->assertFalse(Article::find('testing')->exists());
});
