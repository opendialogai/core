<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\Node\Node;

class ChatbotUser extends Node
{
    public const NEW_USER = 'new';
    public const ONGOING_USER = 'ongoing';
    public const RETURNING_USER = 'returning';

    /** @var string Id of the current conversation */
    private $currentConversationUid;

    /** @var string */
    private $currentIntentUid;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->setGraphType(DGraphClient::USER);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::CHATBOT_USER));
    }

    /**
     * @return bool
     */
    public function isHavingConversation(): bool
    {
        return isset($this->currentConversationUid);
    }

    /**
     * Attaches an entire conversation to the user
     *
     * @param Conversation $conversationForCloning Required to ensure that the new conversation is fully
     * cloned by `UserService.updateUser`
     * @param Conversation $conversationForConnecting Required to ensure that DGraph contains a correct `instance_of`
     * edge between template & instance
     */
    public function setCurrentConversation(Conversation $conversationForCloning, Conversation $conversationForConnecting)
    {
        $currentConversation = clone $conversationForCloning;
        $currentConversation->setConversationType(Model::CONVERSATION_USER);
        $this->createOutgoingEdge(Model::HAVING_CONVERSATION, $currentConversation);

        $currentConversation->createOutgoingEdge(Model::INSTANCE_OF, $conversationForConnecting);
    }

    /**
     * Sets just the uid of the current conversation
     *
     * @param string $currentConversationUid
     * @return ChatbotUser
     */
    public function setCurrentConversationUid(string $currentConversationUid): ChatbotUser
    {
        $this->currentConversationUid = $currentConversationUid;
        return $this;
    }

    /**
     * Returns the uid of the users current intent
     *
     * @return string
     */
    public function getCurrentConversationUid(): string
    {
        return $this->currentConversationUid;
    }

    /**
     * Sets just the uid of the current intent
     *
     * @param string $intentUid
     * @return ChatbotUser
     */
    public function setCurrentIntentUid(string $intentUid): ChatbotUser
    {
        $this->currentIntentUid = $intentUid;
        return $this;
    }

    /**
     * Returns the ID of the user's current intent
     *
     * @return string
     */
    public function getCurrentIntentUid(): string
    {
        return $this->currentIntentUid;
    }

    /**
     * Removes the current conversation and current intent IDs from the user
     * @return void
     */
    public function unsetCurrentConversation(): void
    {
        unset($this->currentIntentUid);
        unset($this->currentConversationUid);
    }

    /**
     * Checks whether the user has a current intent id
     *
     * @return bool
     */
    public function hasCurrentIntent(): bool
    {
        return isset($this->currentIntentUid);
    }

    /**
     * @param AttributeInterface $userAttribute
     * @return UserAttribute
     */
    public function addUserAttribute(AttributeInterface $userAttribute): UserAttribute
    {
        try {
            if ($this->hasUserAttribute($userAttribute->getId())) {
                return $this->setUserAttribute($userAttribute);
            } else {
                $node = new UserAttribute($userAttribute);
                $this->createOutgoingEdge(Model::HAS_ATTRIBUTE, $node);
                return $node;
            }
        } catch (AttributeDoesNotExistException $e) {
            Log::debug($e->getMessage());
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @return UserAttribute
     * @throws AttributeDoesNotExistException
     */
    public function setUserAttribute(AttributeInterface $attribute): UserAttribute
    {
        if ($this->hasUserAttribute($attribute->getId())) {
            /** @var UserAttribute $userAttribute */
            $userAttribute = $this->getAllUserAttributes()->get($attribute->getId(), null);
            $userAttribute->updateInternalAttribute($attribute);
            return $userAttribute;
        } else {
            throw new AttributeDoesNotExistException(
                sprintf("Cannot return attribute with name %s - does not exist", $attribute->getId())
            );
        }
    }

    /**
     * @param $attributeName
     * @return bool
     */
    public function hasUserAttribute($attributeName): bool
    {
        return !is_null($this->getAllUserAttributes()->get($attributeName, null));
    }

    /**
     * @param string $userAttributeId
     * @return AttributeInterface
     * @throws \OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException
     */
    public function getUserAttribute(string $userAttributeId): AttributeInterface
    {
        if ($this->hasUserAttribute($userAttributeId)) {
            /** @var UserAttribute $userAttribute */
            $userAttribute = $this->getAllUserAttributes()->get($userAttributeId, null);
            return $userAttribute->getInternalAttribute();
        } else {
            Log::debug(sprintf("Cannot return attribute with name %s - does not exist", $userAttributeId));
            throw new AttributeDoesNotExistException(
                sprintf("Cannot return attribute with name %s - does not exist", $userAttributeId)
            );
        }
    }

    /**
     * @param string $userAttributeId
     * @return AttributeInterface|null
     * @throws \OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException
     */
    public function getUserAttributeValue(string $userAttributeId): string
    {
        return $this->getUserAttribute($userAttributeId)->getValue();
    }

    /**
     * @return Map
     */
    public function getAllUserAttributes(): Map
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_ATTRIBUTE);
    }
}
