<?php

namespace AloiaCms\Contracts;

use Carbon\Carbon;

/**
 * @deprecated deprecated since version 1.0.0
 */
interface ArticleInterface extends ContentInterface
{

    /**
     * Get the slug of this article
     *
     * @return string
     */
    public function slug(): string;

    /**
     * Get the type of this article
     *
     * @return string
     */
    public function type(): string;

    /**
     * Get the filename of this article
     *
     * @return string
     */
    public function filename(): string;

    /**
     * Get the main image of this article
     *
     * @return string
     * @throws \Exception
     */
    public function image(): string;

    /**
     * Get the path to the thumbnail of this article
     *
     * @return string
     * @throws \Exception
     */
    public function thumbnail(): string;

    /**
     * Get the formatted post date of this article
     *
     * @return string
     */
    public function postDate(): string;

    /**
     * Get a Carbon instance of the post date of this article
     *
     * @return Carbon
     */
    public function rawPostDate(): Carbon;

    /**
     * Get the formatted update date for this article
     *
     * @return string
     */
    public function updatedDate(): string;

    /**
     * Get a Carbon instance of the update date of this article
     *
     * @return Carbon
     */
    public function rawUpdatedDate(): Carbon;

    /**
     * Determine whether this article is published
     *
     * @return bool
     */
    public function isPublished(): bool;

    /**
     * Determine whether this article is scheduled
     *
     * @return bool
     */
    public function isScheduled(): bool;
}
