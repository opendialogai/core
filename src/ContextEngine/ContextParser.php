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
        $matches = explode('.', $attribute);

        switch (count($matches)) {
            case 2:
                [$contextId, $attributeId] = $matches;
                break;
            case 1:
                $contextId = AbstractAttribute::UNDEFINED_CONTEXT;
                $attributeId = $matches[0];
                break;
            default:
                Log::warning(sprintf('Parsing invalid attribute name %s', $attribute));
                $attributeId = AbstractAttribute::INVALID_ATTRIBUTE_NAME;
                $contextId = AbstractAttribute::UNDEFINED_CONTEXT;
        }

        return [$contextId, $attributeId];
    }

    /**T
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
        return self::determineContextAndAttributeId($attribute)[0];
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
        return self::determineContextAndAttributeId($attribute)[1];
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
