<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

interface ContextServiceInterface
{
    const SESSION_CONTEXT      = 'session';
    const CONVERSATION_CONTEXT = 'conversation';

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService): void;

    /**
     * @param ConversationStoreInterface $conversationStore
     */
    public function setConversationStore(ConversationStoreInterface $conversationStore): void;

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function createContext(string $contextId): ContextInterface;

    /**
     * @param ContextInterface $context
     */
    public function addContext(ContextInterface $context): void;

    /**
     * @param string $contextId
     * @return ContextInterface
     * @throws ContextDoesNotExistException
     */
    public function getContext(string $contextId): ContextInterface;

    /**
     * @param AbstractCustomContext[] $contexts
     */
    public function loadCustomContexts(array $contexts): void;

    /**
     * @param AbstractCustomContext $customContext
     */
    public function loadCustomContext($customContext): void;

    /**
     * @param string $contextId
     * @return bool
     */
    public function hasContext(string $contextId): bool;

    /**
     * Saves the attribute provided against a context.
     * If the $attributeName is namespace with a context name, will try to save in the named context.
     * If the named context does not exist or the attribute name is not namespaced,
     * will save against the session context.
     *
     * @param string $attributeName
     * @param $attributeValue
     */
    public function saveAttribute(string $attributeName, $attributeValue): void;

    /**
     * @param string $attributeId
     * @param string $contextId
     * @return AttributeInterface
     */
    public function getAttribute(string $attributeId, string $contextId): AttributeInterface;

    /**
     * Calls @param string $attributeId
     * @param string $contextId
     * @return mixed
     * @see ContextService::getAttribute() to resolve an attribute and returns its concrete value
     *
     */
    public function getAttributeValue(string $attributeId, string $contextId);

    /**
     * @param UtteranceInterface $utterance
     * @return UserContext
     * @throws FieldNotSupported
     */
    public function createUserContext(UtteranceInterface $utterance): UserContext;

    /**
     * Returns all available contexts as an array
     *
     * @return ContextInterface[]
     */
    public function getContexts(): array;

    /**
     * Returns all custom contexts
     *
     * @return ContextInterface[]
     */
    public function getCustomContexts(): array;

    /**
     *  Helper method to return the session context
     *
     * @return BaseContext
     */
    public function getSessionContext(): ContextInterface;

    /**
     *  Helper method to return the user context
     *
     * @return UserContext
     */
    public function getUserContext(): ContextInterface;

    /**
     *  Helper method to return the conversation context
     *
     * @return BaseContext
     */
    public function getConversationContext(): ContextInterface;

    /**
     * Allows a custom user context to be set
     *
     * @param UserContext $userContext
     */
    public function setUserContext(UserContext $userContext);
}
