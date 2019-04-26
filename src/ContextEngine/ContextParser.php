<?php

namespace OpenDialogAi\ContextEngine;

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
        $contextId = null;
        $attributeId = null;

        $matches = explode('.', $attribute, 2);

        if (count($matches) === 2) {
            $contextId = $matches[0];
            $attributeId = $matches[1];
        }

        if (count($matches) === 1) {
            $attributeId = $matches[0];
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
        $contextId = null;
        $matches = explode('.', $attribute);

        if (count($matches) === 2) {
            $contextId = $matches[0];
        }

        if (count($matches) === 1) {
            $contextId = AbstractAttribute::UNDEFINED_CONTEXT;
        }

        return $contextId;
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
        $attributeId = null;

        $matches = explode('.', $attribute);

        if (count($matches) === 2) {
            $attributeId = $matches[1];
        }

        if (count($matches) === 1) {
            $attributeId = $matches[0];
        }

        return $attributeId;
    }
}
