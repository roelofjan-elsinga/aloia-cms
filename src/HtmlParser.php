<?php

namespace AloiaCms;

class HtmlParser
{
    /**
     * @param string $string
     * @param string $tagname
     * @param string $attribute
     * @return array
     */
    public static function getTextBetweenTags(string $string, string $tagname, string $attribute = null): array
    {
        $attribute = $attribute ?? 'textContent';

        $dom_document = new \DOMDocument();
        $dom_document->loadHTML($string);

        return collect($dom_document->getElementsByTagName($tagname))
            ->filter(function ($dom_element) {
                return self::hasSingleChildNode($dom_element);
            })
            ->map(function ($dom_element) use ($attribute) {
                return $dom_element->$attribute;
            })
            ->toArray();
    }

    /**
     * @param string $string
     * @param string $tagname
     * @param string $source
     * @return array
     */
    public static function getTagAttribute(string $string, string $tagname, string $source): array
    {
        $dom_document = new \DOMDocument();
        $dom_document->loadHTML($string);

        return collect($dom_document->getElementsByTagName($tagname))
            ->map(function ($dom_element) use ($source) {
                return $dom_element->getAttribute($source);
            })
            ->toArray();
    }

    /**
     * Determine whether the given DomElement has a single child node
     *
     * @param \DOMElement $dom_element
     * @return bool
     */
    private static function hasSingleChildNode(\DOMElement $dom_element): bool
    {
        return $dom_element->childNodes->length === 1;
    }
}
