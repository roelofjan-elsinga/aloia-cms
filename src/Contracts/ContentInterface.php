<?php

namespace AloiaCms\Contracts;

/**
 * @deprecated deprecated since version 1.0.0
 */
interface ContentInterface
{
    /**
     * Get the title of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function title(): string;

    /**
     * Get the parsed body of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function content(): string;

    /**
     * Get the raw body of this resource
     *
     * @return string
     */
    public function rawContent(): string;

    /**
     * Get the description of this resource
     *
     * @return string
     * @throws \Exception
     */
    public function description(): string;

    /**
     * Get the canonical if it's set
     *
     * @return null|string
     */
    public function canonicalLink(): ?string;

    /**
     * Get the fileType of this article
     *
     * @return string
     */
    public function fileType(): string;
}
