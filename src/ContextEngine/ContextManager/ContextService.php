<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\MessageHistory\MessageHistoryContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ContextService implements ContextServiceInterface
{
    public static $coreContexts = [
        UserContext::USER_CONTEXT,
        IntentContext::INTENT_CONTEXT,
        MessageHistoryContext::MESSAGE_HISTORY_CONTEXT,
        self::SESSION_CONTEXT,
        self::CONVERSATION_CONTEXT
    ];

    /* @var Map $activeContexts - a container for contexts that the service is managing */
    private $activeContexts;

    /* @var UserService */
    private $userService;

    /** @var ConversationStoreInterface */
    private $conversationStore;

    /**
     * ContextService constructor.
     */
    public function __construct()
    {
        $this->activeContexts = new Map();
    }

    /**
     * @inheritDoc
     */
    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @inheritDoc
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore): void
    {
        $this->conversationStore = $conversationStore;
    }

    /**
     * @inheritDoc
     */
    public function createContext(string $contextId): ContextInterface
    {
        $newContext = new BaseContext($contextId);
        $this->addContext($newContext);
        return $newContext;
    }

    /**
     * @inheritDoc
     */
    public function addContext(ContextInterface $context): void
    {
        $this->activeContexts->put($context->getId(), $context);
    }

    /**
     * @inheritDoc
     */
    public function getContext(string $contextId): ContextInterface
    {
        if ($this->hasContext($contextId)) {
            return $this->activeContexts->get($contextId);
        }

        $message = sprintf('Cannot get context with name %s - does not exist', $contextId);
        Log::debug($message);
        throw new ContextDoesNotExistException($message);
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

    /**
     * @inheritDoc
     */
    public function hasContext(string $contextId): bool
    {
        return $this->activeContexts->hasKey($contextId);
    }

    /**
     * @inheritDoc
     */
    public function saveAttribute(string $attributeName, $attributeValue): void
    {
        try {
            $context = $this->getContext(ContextParser::determineContextId($attributeName));
        } catch (ContextDoesNotExistException $e) {
            Log::debug(
                sprintf('Trying to save attribute without context id, using session context %s', $attributeName)
            );
            $context = $this->getSessionContext();
        }

        $attributeId = ContextParser::determineAttributeId($attributeName);
        try {
            $attribute = AttributeResolver::getAttributeFor($attributeId, $attributeValue);
        } catch (AttributeIsNotSupported $e) {
            Log::debug(sprintf('Trying to save unsupported attribute, using StringAttribute %s', $attributeName));
            $attribute = AttributeResolver::getAttributeFor($attributeId, $attributeValue);
        }

        $context->addAttribute($attribute);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attributeId, string $contextId): AttributeInterface
    {
        /* @var ContextInterface $context */
        $context = ($this->hasContext($contextId)) ? $this->getContext($contextId) : $this->getSessionContext();

        Log::debug(
            sprintf('Attempting to retrieve attribute %s in context %s', $attributeId, $context->getId())
        );

        try {
            return $context->getAttribute($attributeId);
        } catch (AttributeDoesNotExistException $e) {
            Log::warning(
                sprintf('Attribute %s does not exist in context %s', $attributeId, $context->getId())
            );

            return AttributeResolver::getAttributeFor($attributeId, '');
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValue(string $attributeId, string $contextId, array $index = [])
    {
        if (!$index) {
            return $this->getAttribute($attributeId, $contextId)->getValue();
        }
        return $this->getAttribute($attributeId, $contextId)->getValue($index);
    }

    /**
     * @inheritDoc
     */
    public function createUserContext(UtteranceInterface $utterance): UserContext
    {
        $chatbotUser = $this->userService->createOrUpdateUser($utterance);
        $userContext = new UserContext($chatbotUser, $this->userService, $this->conversationStore);
        $this->addContext($userContext);
        return $userContext;
    }

    /**
     * @inheritDoc
     */
    public function getContexts(): array
    {
        return $this->activeContexts->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getCustomContexts(): array
    {
        return $this->activeContexts->filter(static function ($context) {
            return !in_array($context, self::$coreContexts, true);
        })->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getSessionContext(): ContextInterface
    {
        return $this->getContext(self::SESSION_CONTEXT);
    }

    /**
     * @inheritDoc
     */
    public function getUserContext(): ContextInterface
    {
        return $this->getContext(UserContext::USER_CONTEXT);
    }

    /**
     * @inheritDoc
     */
    public function getConversationContext(): ContextInterface
    {
        return $this->getContext(self::CONVERSATION_CONTEXT);
    }

    /**
     * @inheritDoc
     */
    public function setUserContext(UserContext $userContext)
    {
        $this->addContext($userContext);
    }
}
