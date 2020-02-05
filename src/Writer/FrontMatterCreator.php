<?php


namespace FlatFileCms\Writer;

use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class FrontMatterCreator
{
    private $matter = [];

    private $content = '';

    /**
     * FrontMatterCreator constructor.
     * @param array $matter
     * @param string $content
     */
    private function __construct(array $matter, string $content)
    {
        $this->matter = $matter;
        $this->content = $content;
    }

    /**
     * Provide the front matter and file content
     *
     * @param array $matter
     * @param string $content
     * @return static
     */
    public static function seed(array $matter, string $content)
    {
        return new static($matter, $content);
    }

    /**
     * Create a data file including the front matter
     *
     * @return string
     */
    public function create(): string
    {
        $matter = Collection::make($this->matter)
            ->mapWithKeys(function ($value, $key) {
                return [$this->toSnakeCase($key) => $value];
            })
            ->toArray();

        $front_matter_string = "";

        if (count($matter) > 0) {
            $front_matter_string = "---\n";
            $front_matter_string .= Yaml::dump($matter);
            $front_matter_string .= "---\n\n";
        }

        $front_matter_string .= $this->content;

        return $front_matter_string;
    }

    /**
     * Convert camelcase strings to snake case
     *
     * @param $input
     * @return string
     */
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
