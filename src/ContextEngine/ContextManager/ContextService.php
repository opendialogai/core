<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

use Ds\Map;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class ContextService
{
    const SESSION_CONTEXT      = 'session';
    const CONVERSATION_CONTEXT = 'conversation';

    public static $coreContexts = [UserContext::USER_CONTEXT, self::SESSION_CONTEXT, self::CONVERSATION_CONTEXT];

    /* @var Map $activeContexts - a container for contexts that the service is managing */
    private $activeContexts;

    /* @var UserService */
    private $userService;

    /**
     * ContextService constructor.
     */
    public function __construct()
    {
        $this->activeContexts = new Map();
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function createContext(string $contextId): ContextInterface
    {
        $newContext = new BaseContext($contextId);
        $this->addContext($newContext);
        return $newContext;
    }

    /**
     * @param ContextInterface $context
     */
    public function addContext(ContextInterface $context): void
    {
        $this->activeContexts->put($context->getId(), $context);
    }

    /**
     * @param string $contextId
     * @throws ContextDoesNotExistException
     * @return ContextInterface
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
     * @param AbstractCustomContext[] $contexts
     */
    public function loadCustomContexts(array $contexts): void
    {
        foreach ($contexts as $context) {
            $this->loadCustomContext($context);
        }
    }

    /**
     * @param AbstractCustomContext $customContext
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
            Log::warning(sprintf(
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
        } catch (Exception $e) {
            Log::warning(sprintf('Error while adding context %s - %s', $customContext, $e->getMessage()));
        }
    }

    /**
     * @param string $contextId
     * @return bool
     */
    public function hasContext(string $contextId): bool
    {
        return $this->activeContexts->hasKey($contextId);
    }

    /**
     * Saves the attribute provided against a context.
     * If the $attributeName is namespace with a context name, will try to save in the named context.
     * If the named context does not exist or the attribute name is not namespaced, will save against the session context
     *
     * @param string $attributeName
     * @param $attributeValue
     */
    public function saveAttribute(string $attributeName, $attributeValue): void
    {
        try {
            $context = $this->getContext(ContextParser::determineContextId($attributeName));
        } catch (ContextDoesNotExistException $e) {
            Log::debug(sprintf('Trying to save attribute without context id, using session context %s', $attributeName));
            $context = $this->getSessionContext();
        }

        $attributeId = ContextParser::determineAttributeId($attributeName);
        try {
            $attribute = AttributeResolver::getAttributeFor($attributeId, $attributeValue);
        } catch (AttributeIsNotSupported $e) {
            Log::debug(sprintf('Trying to save unsupported attribute, using StringAttribute %s', $attributeName));
            $attribute = new StringAttribute($attributeId, $attributeValue);
        }

        $context->addAttribute($attribute);
    }

    /**
     * @param string $attributeId
     * @param string $contextId
     * @return AttributeInterface
     * @throws ContextDoesNotExistException
     */
    public function getAttribute(string $attributeId, string $contextId): AttributeInterface
    {
        if ($this->hasContext($contextId)) {
            /* @var ContextInterface $context */
            $context = $this->getContext($contextId);
            Log::debug(
                sprintf('Attempting to retrieve attribute %s in context %s', $attributeId, $context->getId())
            );
            return $context->getAttribute($attributeId);
        }

        throw new ContextDoesNotExistException(
            sprintf('Context %s for attribute %s not available.', $contextId, $attributeId)
        );
    }

    /**
     * Calls @see ContextService::getAttribute() to resolve an attribute and returns its concrete value
     *
     * @param string $attributeId
     * @param string $contextId
     * @return mixed
     */
    public function getAttributeValue(string $attributeId, string $contextId)
    {
        return $this->getAttribute($attributeId, $contextId)->getValue();
    }

    /**
     * @param UtteranceInterface $utterance
     * @return UserContext
     * @throws FieldNotSupported
     */
    public function createUserContext(UtteranceInterface $utterance): UserContext
    {
        $userContext = new UserContext($this->userService->createOrUpdateUser($utterance), $this->userService);
        $this->addContext($userContext);
        return $userContext;
    }

    /**
     * Returns all available contexts as an array
     *
     * @return ContextInterface[]
     */
    public function getContexts(): array
    {
        return $this->activeContexts->toArray();
    }

    /**
     * Returns all custom contexts
     *
     * @return ContextInterface[]
     */
    public function getCustomContexts(): array
    {
        return $this->activeContexts->filter(static function ($context) {
            return !in_array($context, self::$coreContexts, true);
        })->toArray();
    }

    /**
     *  Helper method to return the session context
     *
     * @return BaseContext
     */
    public function getSessionContext(): ContextInterface
    {
        return $this->getContext(self::SESSION_CONTEXT);
    }

    /**
     *  Helper method to return the user context
     *
     * @return UserContext
     */
    public function getUserContext(): ContextInterface
    {
        return $this->getContext(UserContext::USER_CONTEXT);
    }

    /**
     *  Helper method to return the conversation context
     *
     * @return BaseContext
     */
    public function getConversationContext(): ContextInterface
    {
        return $this->getContext(self::CONVERSATION_CONTEXT);
    }
}
