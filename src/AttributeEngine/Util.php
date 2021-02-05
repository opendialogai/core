<?php

namespace OpenDialogAi\AttributeEngine;

/**
 * Collection of Util/Helper functions.
 *
 */
class Util
{
    public static function decode($json)
    {
        return json_decode(htmlspecialchars_decode(
            $json,
            ENT_QUOTES
        ), true);
    }

    public static function encode($array)
    {
        return htmlspecialchars(json_encode($array), ENT_QUOTES);
    }

    /**
     * Parse a string into an array.
     *
     * @param string $subject
     *   The subject string.
     *
     * @return array|bool
     *   The array.
     */
    public static function parse(string $subject)
    {
        $result = [];

        \preg_match_all('~[^\[\]]+|\[(?<nested>(?R)*)\]~', $subject, $matches);

        foreach (\array_filter($matches['nested']) as $match) {
            $item = [];
            $position = \strpos($match, '[');

            if (false !== $position) {
                $item['value'] = \substr($match, 0, $position);
            } else {
                $item['value'] = $match;
            }

            if ([] !== $children = Util::parse($match)) {
                $item['children'] = $children;
            }

            $result[] = $item;
        }

        return $result;
    }
}
