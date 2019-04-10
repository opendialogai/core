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

    /**
     * @return bool
     */
    public function isHavingConversation() : bool
    {
        if ($this->hasOutgoingEdgeWithRelationship(Model::HAVING_CONVERSATION)) {
            return true;
        }
        return false;
    }

    /**
     * @param Conversation $conversation
     */
    public function setCurrentConversation(Conversation $conversation)
    {
        $currentConversation = clone $conversation;
        $currentConversation->setConversationType(Model::CONVERSATION_USER);
        $this->createOutgoingEdge(Model::HAVING_CONVERSATION, $currentConversation);
    }

    /**
     * @return Conversation
     */
    public function getCurrentConversation(): Conversation
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::HAVING_CONVERSATION)->first()->value;
    }

    /**
     * @param Intent $intent
     */
    public function setCurrentIntent(Intent $intent)
    {
        $this->getCurrentConversation()->createOutgoingEdge(Model::CURRENT_INTENT, $intent);
    }

    /**
     * @return Intent
     */
    public function getCurrentIntent(): Intent
    {
        if ($this->isHavingConversation()) {
            return $this->getCurrentConversation()
                ->getNodesConnectedByOutgoingRelationship(Model::CURRENT_INTENT)
                ->first()
                ->value;
        }
    }

    /**
     * @return bool
     */
    public function hasCurrentIntent() : bool
    {
        if ($this->isHavingConversation()) {
            if ($this->getCurrentConversation()->hasOutgoingEdgeWithRelationship(Model::CURRENT_INTENT)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $uid
     * @return Intent
     */
    public function getIntentByUid($uid): Intent
    {
        return $this->getCurrentConversation()->getIntentByUid($uid);
    }
}
