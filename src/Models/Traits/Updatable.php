<?php

namespace FlatFileCms\Models\Traits;

use Carbon\Carbon;

trait Updatable
{
    /**
     * Set the update date
     *
     * @param Carbon $update_date
     * @return $this
     */
    public function setUpdateDate(Carbon $update_date)
    {
        $this->matter['update_date'] = $update_date->toDateTimeString();

        return $this;
    }

    /**
     * Get the update date
     *
     * @return Carbon|null
     */
    public function getUpdateDate()
    {
        if (!isset($this->matter['update_date'])) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $this->matter['update_date']);
    }
}
