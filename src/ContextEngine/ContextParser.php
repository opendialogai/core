<?php

namespace OpenDialogAi\ContextEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AbstractAttribute;

abstract class ContextParser
{
    /**
     * Attempts to work out the context and attribute ID for a fully qualified attribute name.
     * For example, an attribute with the name user.name would return:
     * ['user', 'name']
     *
     * If no context is included with the attribute @see AbstractAttribute::UNDEFINED_CONTEXT is returned for the
     * context id
     *
     * @param $attribute
     * @return array The context and attribute ids in an array where the first value is the context id
     */
    public static function determineContextAndAttributeId($attribute): array
    {
        $parsedAttribute = self::parseAttributeName($attribute);
        return [$parsedAttribute->contextId, $parsedAttribute->attributeId];
    }

    public static function parseAttributeName($attribute) : ParsedAttributeName
    {
        $matches = explode('.', $attribute);

        $parsedAttribute = new ParsedAttributeName();

        switch (count($matches)) {
            case 2:
                $parsedAttribute->setContextId($matches[0]);
                self::parseArrayNotation($matches[1], $parsedAttribute);
                break;
            case 1:
                self::parseArrayNotation($matches[0], $parsedAttribute);
                $parsedAttribute->setAttributeId($matches[0]);
                break;
            default:
                Log::warning(sprintf('Parsing invalid attribute name %s', $attribute));
        }

        return $parsedAttribute;
    }

    /**
     * @param string $attributeId
     * @param ParsedAttributeName $parsedAttribute
     * @return ParsedAttributeName
     */
    private static function parseArrayNotation($attributeId, $parsedAttribute): ParsedAttributeName
    {
        $split = preg_split('/[[\]\]]/', $attributeId, null, PREG_SPLIT_NO_EMPTY);
        $parsedAttribute->attributeId = $split[0];
        return $parsedAttribute;
    }

    /**
     * Attempts to work out the context ID for a fully qualified attribute name.
     * For example, an attribute with the name user.name would return: 'user'
     *
     * If no context is included with the attribute @see AbstractAttribute::UNDEFINED_CONTEXT is returned for the
     * context id
     *
     * @param $attribute
     * @return string
     */
    public static function determineContextId($attribute): string
    {
        $parsedAttributeName = self::parseAttributeName($attribute);
        return $parsedAttributeName->contextId;
    }

    /**
     * Attempts to work out the attribute ID for a fully qualified attribute name.
     * For example, an attribute with the name user.name would return: 'name'
     *
     * @param $attribute
     * @return string
     */
    public static function determineAttributeId($attribute): string
    {
        $parsedAttributeName = self::parseAttributeName($attribute);
        return $parsedAttributeName->attributeId;
    }

    /**
     * Checks whether the provided $attribute name contains a context ID
     *
     * @param string $attribute
     * @return bool
     */
    public static function containsContextName(string $attribute):  bool
    {
        return self::determineContextId($attribute) !== AbstractAttribute::UNDEFINED_CONTEXT;
    }
}
