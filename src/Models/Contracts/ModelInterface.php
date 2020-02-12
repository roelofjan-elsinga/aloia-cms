<?php


namespace FlatFileCms\Models\Contracts;

use Illuminate\Support\Collection;

interface ModelInterface
{
    public static function find(string $file_name): ModelInterface;

    public static function all(): Collection;

    public function matter(): array;

    public function addMatter(string $key, string $value): ModelInterface;

    public function setMatter(array $matter): ModelInterface;

    public function body(): string;

    public function setBody(string $body): ModelInterface;

    public function extension(): string;

    public function setExtension(string $extension): ModelInterface;

    public function save(): ModelInterface;

    public function exists(): bool;

    public function delete(): bool;
}
