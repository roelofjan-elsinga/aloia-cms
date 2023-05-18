<?php


namespace AloiaCms\Writer;

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
            ->keysToSnakeCase()
            ->toArray();

        $front_matter_string = "";

        if (count($matter) > 0) {
            $front_matter_string = "---\n";
            $front_matter_string .= Yaml::dump($matter);
            $front_matter_string .= "---\n";
        }

        $content = ltrim($this->content, "\n");

        if (strlen($content) > 0) {
            $front_matter_string .= "\n" . ltrim($this->content, "\n");
        }

        return $front_matter_string;
    }
}
