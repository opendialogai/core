<?php

namespace OpenDialogAi\ContextEngine\ContextService;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\MessageHistory\MessageHistoryContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;


class CoreContextService extends BasicContextService
{
    public const SESSION_CONTEXT = 'session';
    public const CONVERSATION_CONTEXT = 'conversation';

    public static $coreContexts = [
        IntentContext::INTENT_CONTEXT,
        MessageHistoryContext::MESSAGE_HISTORY_CONTEXT,
        self::SESSION_CONTEXT,
        self::CONVERSATION_CONTEXT,
        UserContext::USER_CONTEXT
    ];

    /**
     * ContextService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

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


    /**
     * @inheritDoc
     */
    public function getCustomContexts(): array
    {
        return $this->contexts->filter(static function ($context) {
            return !in_array($context, self::$coreContexts, true);
        })->toArray();
    }

    /**
     * @inheritDoc
     */
    public function loadContexts(array $contexts): void
    {
        foreach ($contexts as $context) {
            $this->loadContext($context);
        }
    }

    /**
     * @inheritDoc
     */
    public function loadContext($context): void
    {
        if (!class_exists($context)) {
            Log::warning(sprintf('Not adding context %s, class does not exist', $context));
            return;
        }

        if (empty($context::getComponentId())) {
            Log::warning(sprintf('Not adding context %s, it has no component ID', $context));
            return;
        }

        if ($this->hasContext($context::getComponentId())) {
            Log::warning(
                sprintf(
                    'Not adding context %s, context with that ID is already registered',
                    $context
                )
            );
            return;
        }

        Log::debug(sprintf('Registering context %s', $context));
        try {
            /** @var AbstractContext $context */
            $context = resolve($context);

            $context::getComponentData();

            $this->addContext($context);
        } catch (\Exception $e) {
            Log::warning(sprintf('Error while adding context %s - %s', $context, $e->getMessage()));
        }
    }
}
