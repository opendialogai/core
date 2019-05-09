<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Parses button values to get attribute name and values.
 *
 * Button values should be in the format {attributeName}.{attributeValue}
 *
 * If {attributeName} is not included, 'button_value' will be used
 */
abstract class ButtonValueParser
{
    const BUTTON_VALUE = 'button_value';
    const ATTRIBUTE_NAME = 'attribute_name';
    const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * Parses the button value and returns an array in the format:
     * [
     *  'attribute_name'  => $attributeName,
     *  'attribute_value' => $attributeValue
     * ]
     *
     * If no attribute name can be established, 'button_value' is used
     *
     * @param $value
     * @return array
     */
    public static function parseButtonValue($value): array
    {
        $matches = explode('.', $value);

        switch (count($matches)) {
            case 1:
                $attributeName = self::BUTTON_VALUE;
                $attributeValue = $value;
                break;
            case 2:
                [$attributeName, $attributeValue] = $matches;
                break;
            default:
                Log::warning(sprintf('Parsing invalid button value %s', $value));
                $attributeName = self::BUTTON_VALUE;
                $attributeValue = $value;
                break;
        }

        return [
            self::ATTRIBUTE_NAME  => $attributeName,
            self::ATTRIBUTE_VALUE => $attributeValue
        ];
    }
}
