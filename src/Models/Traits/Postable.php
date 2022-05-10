<?php

namespace AloiaCms\Models\Traits;

use Carbon\Carbon;

trait Postable
{
    /**
     * Set the post date
     *
     * @param Carbon $post_date
     * @return $this
     */
    public function setPostDate(Carbon $post_date)
    {
        $this->matter['post_date'] = $post_date->toDateString();

        return $this;
    }

    /**
     * Get the post date
     *
     * @return Carbon|null
     */
    public function getPostDate(): ?Carbon
    {
        if (!isset($this->matter['post_date']) || empty($this->matter['post_date'])) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $this->matter['post_date']);
    }
}
