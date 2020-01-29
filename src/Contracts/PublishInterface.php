<?php

namespace FlatFileCms\Contracts;

interface PublishInterface
{
    /**
     * Determine whether this object is published
     *
     * @return bool
     */
    public function isPublished(): bool;
}
