<?php

namespace OpenDialogAi\ContextEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\AbstractAttribute;

abstract class ContextParser
{
    public static function parseAttributeName($attribute) : ParsedAttributeName
    {
        $matches = explode('.', $attribute);

        $parsedAttribute = new ParsedAttributeName();

        if (self::isValidContext($matches[0])) {
            $parsedAttribute->setContextId($matches[0]);
            $parsedAttribute->setAttributeId($matches[1]);

            if (count($matches)>2) {
                $parsedAttribute->setAccessor(array_slice($matches, 2));
            }
        } else {
            Log::warning(sprintf('Parsed an invalid context id - %s', $matches[0]));

            $parsedAttribute->attributeId = $matches[0];

            if (count($matches) == 2) {
                $parsedAttribute->setAccessor(array_slice($matches, 1));
            } else if (count($matches) > 2) {
                $parsedAttribute->setAccessor(array_slice($matches, 2));
            }
        }

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

    /**
     * Checks if the given context id is valid and bound
     *
     * @param string $contextId
     * @return bool
     */
    private static function isValidContext($contextId): bool
    {
        return ContextService::hasContext($contextId);
    }
}
