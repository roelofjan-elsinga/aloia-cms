<?php

namespace AloiaCms\Tests\Models;

use AloiaCms\Events\PostModelDeleted;
use AloiaCms\Events\PostModelRenamed;
use AloiaCms\Events\PostModelSaved;
use AloiaCms\Events\PreModelDeleted;
use AloiaCms\Events\PreModelRenamed;
use AloiaCms\Events\PreModelSaved;
use AloiaCms\Models\Article;
use AloiaCms\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;

class ModelEventTest extends TestCase
{
    public function testPreAndPostSaveEventsAreDispatchedForModels()
    {
        Event::fake();

        Article::find('article')
            ->setPostDate(Carbon::now())
            ->save();

        Event::assertDispatched(PreModelSaved::class, 1);
        Event::assertDispatched(PostModelSaved::class, 1);
    }

    public function testPreAndPostDeleteEventsAreDispatchedForModels()
    {
        Event::fake();

        Article::find('article')
            ->setPostDate(Carbon::now())
            ->save();

        Article::find('article')->delete();

        Event::assertDispatched(PreModelDeleted::class, 1);
        Event::assertDispatched(PostModelDeleted::class, 1);
    }

    public function testPreAndPostRenameEventsAreDispatchedForModels()
    {
        Event::fake();

        Article::find('article')
            ->setPostDate(Carbon::now())
            ->save();

        $renamed_article = Article::find('article')
            ->rename("test_article")
            ->save();

        Event::assertDispatched(PreModelRenamed::class, 1);
        Event::assertDispatched(PostModelRenamed::class, 1);

        $this->assertSame("test_article", $renamed_article->filename());
    }
}
