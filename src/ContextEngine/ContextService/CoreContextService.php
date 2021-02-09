<?php

namespace OpenDialogAi\ContextEngine\ContextService;

use Ds\Map;
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

    public static $coreContexts = [
        IntentContext::INTENT_CONTEXT,
        MessageHistoryContext::MESSAGE_HISTORY_CONTEXT,
        self::SESSION_CONTEXT,
        self::CONVERSATION_CONTEXT
    ];

    /* @var Map $activeContexts - a container for contexts that the service is managing */
    private $activeContexts;

    /**
     * ContextService constructor.
     */
    public function __construct()
    {
        $this->activeContexts = new Map();
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
        dump($this->activeContexts);
        return $this->activeContexts->filter(static function ($context) {
            return !in_array($context, self::$coreContexts, true);
        })->toArray();
    }

    /**
     * @inheritDoc
     */
    public function loadCustomContexts(array $contexts): void
    {
        foreach ($contexts as $context) {
            $this->loadCustomContext($context);
        }
    }

    /**
     * @inheritDoc
     */
    public function loadCustomContext($customContext): void
    {
        if (!class_exists($customContext)) {
            Log::warning(sprintf('Not adding custom context %s, class does not exist', $customContext));
            return;
        }

        if (empty($customContext::$name)) {
            Log::warning(sprintf('Not adding custom context %s, it has no name', $customContext));
            return;
        }

        if ($this->hasContext($customContext::$name)) {
            Log::warning(
                sprintf(
                    'Not adding custom context %s, context with that name is already registered',
                    $customContext
                )
            );
            return;
        }

        Log::debug(sprintf('Registering custom context %s', $customContext));
        try {
            /** @var AbstractCustomContext $context */
            $context = new $customContext();
            $context->loadAttributes();
            $this->addContext($context);
        } catch (\Exception $e) {
            Log::warning(sprintf('Error while adding context %s - %s', $customContext, $e->getMessage()));
        }
    }
}
