<?php


namespace FlatFileCms\Models;

interface ModelInterface
{
    public static function open(string $file_name): ModelInterface;

    public function matter(): array;

    public function addMatter(string $key, string $value): ModelInterface;

    public function setMatter(array $matter): ModelInterface;

    public function body(): string;

    public function setBody(string $body): ModelInterface;

    public function extension(): string;

    public function setExtension(string $extension): ModelInterface;

    public function save(): ModelInterface;

    public function exists(): bool;

    public static function fileExists(string $file_name): bool;
}
