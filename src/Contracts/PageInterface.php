<?php

namespace FlatFileCms\Contracts;

/**
 * @deprecated deprecated since version 1.0.0
 */
interface PageInterface extends ArticleInterface
{

    /**
     * Get the author of this page
     *
     * @return string
     */
    public function author(): string;

    /**
     * Get the summary of this page
     *
     * @return string
     */
    public function summary(): string;

    /**
     * Get the template name of this page
     *
     * @return string
     */
    public function templateName(): string;

    /**
     * Determine whether this page is in the menu
     *
     * @return bool
     */
    public function isInMenu(): bool;

    /**
     * Determine whether this page is the homepage
     *
     * @return bool
     */
    public function isHomepage(): bool;

    /**
     * Get the keywords of this page
     *
     * @return string
     */
    public function keywords(): string;

    /**
     * Get the category of this page
     *
     * @return string
     */
    public function category(): string;
}
