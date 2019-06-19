<?php

namespace FlatFileCms\Contracts;

interface ContentInterface
{
    public function title(): string;

    public function content(): string;

    public function rawContent(): string;

    public function description(): string;

    public function canonicalLink(): ?string;
}
