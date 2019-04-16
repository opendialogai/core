<?php

namespace OpenDialogAi\ContextEngine;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;

class ContextParser
{
    public static function determineContext($attribute, &$contextId, &$attributeId)
    {
        $matches = explode('.', $attribute);

        if (count($matches) == 2) {
            $contextId = $matches[0];
            $attributeId = $matches[1];
        }

        if (count($matches) == 1) {
            $attributeId = $matches[0];
            $contextId = ContextService::UNDEFINED_CONTEXT;
        }
    }
}
