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
}
