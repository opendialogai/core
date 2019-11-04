<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

class ChatbotUser extends Node
{
    /** @var string Id of the current conversation */
    private $currentConversationUid;

    /** @var string */
    private $currentIntentUid;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CHATBOT_USER));
    }

    /**
     * @return bool
     */
    public function isHavingConversation() : bool
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
}
