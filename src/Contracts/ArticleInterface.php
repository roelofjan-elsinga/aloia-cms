<?php

namespace FlatFileCms\Contracts;

use Carbon\Carbon;

interface ArticleInterface extends ContentInterface
{
    public function slug(): string;

    public function filename(): string;

    public function image(): string;

    public function thumbnail(): string;

    public function postDate(): string;

    public function rawPostDate(): Carbon;

    public function updatedDate(): string;

    public function rawUpdatedDate(): Carbon;

    public function isPublished(): bool;

    public function isScheduled(): bool;
}
