<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Parses button values to get attribute name and values.
 *
 * Button values should be in the format {attributeName}.{attributeValue}
 *
 * If {attributeName} is not included, 'callback_value' will be used
 */
abstract class CallbackValueParser
{
    const CALLBACK_VALUE = 'callback_value';
    const ATTRIBUTE_NAME = 'attribute_name';
    const ATTRIBUTE_VALUE = 'attribute_value';

    const DOT = '.';
    const DOT_REPLACE = '{dot}';
    const ESCAPE_STRING = "\.";

    /**
     * Parses the callback value and returns an array in the format:
     * [
     *  'attribute_name'  => $attributeName,
     *  'attribute_value' => $attributeValue
     * ]
     *
     * If no attribute name can be established, 'callback_value' is used
     *
     * @param $value
     * @return array
     */
    public static function parseCallbackValue($value): array
    {
        $value = self::replaceEscaped($value);
        $matches = explode('.', $value);

        switch (count($matches)) {
            case 1:
                $attributeName = self::CALLBACK_VALUE;
                $attributeValue = self::restoreEscaped($value);
                break;
            case 2:
                $attributeName = $matches[0];
                $attributeValue = self::restoreEscaped($matches[1]);
                break;
            default:
                Log::warning(sprintf('Parsing invalid button value %s', $value));
                $attributeName = self::CALLBACK_VALUE;
                $attributeValue = $value;
                break;
        }

        return [
            self::ATTRIBUTE_NAME  => $attributeName,
            self::ATTRIBUTE_VALUE => $attributeValue
        ];
    }

    /**
     * Replaces all \. character sequences with {dot} so that it doesn't affect splitting
     *
     * @param $value
     * @return string
     */
    private static function replaceEscaped($value)
    {
        return str_replace(self::ESCAPE_STRING, self::DOT_REPLACE, $value);
    }

    /**
     * Restores the escaped string {dot} with an actual .
     * @param string $value
     * @return string
     */
    private static function restoreEscaped(string $value)
    {
        return str_replace(self::DOT_REPLACE, self::DOT, $value);
    }
}
