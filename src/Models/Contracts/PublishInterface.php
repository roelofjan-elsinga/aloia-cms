<?php

namespace AloiaCms\Models\Contracts;

use Illuminate\Support\Collection;

interface PublishInterface
{
    /**
     * Determine whether this object is published
     *
     * @return bool
     */
    public function isPublished(): bool;

    /**
     * Retrieve all published models
     *
     * @return Collection
     */
    public static function published(): Collection;

    /**
     * Determine whether this article is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool;

    /**
     * Return all scheduled models
     *
     * @return Collection
     */
    public static function scheduled(): Collection;
}
