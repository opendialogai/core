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
        $matches = explode('.', $value);

        switch (count($matches)) {
            case 1:
                $attributeName = self::CALLBACK_VALUE;
                $attributeValue = $value;
                break;
            case 2:
                [$attributeName, $attributeValue] = $matches;
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
}
