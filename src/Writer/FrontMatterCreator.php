<?php


namespace FlatFileCms\Writer;

use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class FrontMatterCreator
{
    private $matter = [];

    private $content = '';

    private function __construct(array $matter, string $content)
    {
        $this->matter = $matter;
        $this->content = $content;
    }

    public static function seed(array $matter, string $content)
    {
        return new static($matter, $content);
    }

    public function create(): string
    {
        $matter = Collection::make($this->matter)
            ->mapWithKeys(function ($value, $key) {
                return [$this->toSnakeCase($key) => $value];
            })
            ->toArray();

        $front_matter_string = "---\n";
        $front_matter_string .= Yaml::dump($matter);
        $front_matter_string .= "---\n\n";

        $front_matter_string .= $this->content;

        return $front_matter_string;
    }

    private function toSnakeCase($input)
    {
        if (preg_match('/[A-Z]/', $input) === 0) {
            return $input;
        }

        $pattern = '/([a-z])([A-Z])/';

        return strtolower(preg_replace_callback($pattern, function ($a) {
            return $a[1] . "_" . strtolower($a[2]);
        }, $input));
    }
}
