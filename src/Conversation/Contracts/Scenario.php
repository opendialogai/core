<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

interface Scenario extends ConversationObject
{
    public function getConversations(): ConversationCollection;

    public function setConversations(ConversationCollection $conversations);
}
