<?php


namespace OpenDialogAi\Core\Conversation;


use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

class ChatbotUser extends Node
{
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CHATBOT_USER));
    }

    public function isHavingConversation()
    {
        //Retrieve the current user from DGraph and determine if there is an ongoing conversation


    }

    public function setCurrentConversation(Conversation $conversation)
    {
        $currentConversation = clone $conversation;
        $currentConversation->setConversationType(Model::CONVERSATION_USER);
        $this->createOutgoingEdge(Model::HAVING_CONVERSATION, $currentConversation);
    }

    public function getCurrentConversation(): Conversation
    {
    }

    public function completeConversation($conversationId)
    {
    }

    public function setCurrentIntent(Intent $intent)
    {
    }
}
