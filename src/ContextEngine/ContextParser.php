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
    public static function determineContext($attribute)
    {
        $contextId = null;
        $attributeId = null;

        $matches = explode('.', $attribute);

        if (count($matches) == 2) {
            $contextId = $matches[0];
            $attributeId = $matches[1];
        }

        if (count($matches) == 1) {
            $attributeId = $matches[0];
            $contextId = AbstractAttribute::UNDEFINED_CONTEXT;
        }

        return [$contextId, $attributeId];
    }
}
