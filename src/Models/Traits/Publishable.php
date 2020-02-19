<?php


namespace AloiaCms\Models\Traits;

use Illuminate\Support\Collection;

trait Publishable
{
    /**
     * Determine whether this article is published
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->matter['is_published'] ?? false;
    }

    /**
     * Return all published models
     *
     * @return Collection
     */
    public static function published(): Collection
    {
        return self::all()
            ->filter(function ($model) {
                return $model->isPublished();
            })
            ->values();
    }

    /**
     * Determine whether this article is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->matter['is_scheduled'] ?? false;
    }

    /**
     * Return all scheduled models
     *
     * @return Collection
     */
    public static function scheduled(): Collection
    {
        return self::all()
            ->filter(function ($model) {
                return $model->isScheduled();
            })
            ->values();
    }
}
