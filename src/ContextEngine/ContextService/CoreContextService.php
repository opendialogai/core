<?php

namespace OpenDialogAi\ContextEngine\ContextService;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\MessageHistory\MessageHistoryContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;


class CoreContextService extends BasicContextService
{
    public const SESSION_CONTEXT = 'session';
    public const CONVERSATION_CONTEXT = 'conversation';

    public function saveAttribute(string $attributeName, $attributeValue): void
    {
        try {
            $context = $this->getContext(ContextParser::determineContextId($attributeName));
        } catch (ContextDoesNotExistException $e) {
            Log::debug(
                sprintf('Trying to save attribute without context id, using session context %s', $attributeName)
            );
            // If we cannot determine a specific context we save to the session context
            $context = $this->getContext(self::SESSION_CONTEXT);
        }

        $attributeId = ContextParser::determineAttributeId($attributeName);
        $attribute = AttributeResolver::getAttributeFor($attributeId, $attributeValue);

        $context->addAttribute($attribute);
    }

    /**
     * @inheritDoc
     */
    public function getSessionContext(): Context
    {
        return $this->getContext(self::SESSION_CONTEXT);
    }
}
