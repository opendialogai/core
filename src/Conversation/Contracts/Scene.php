<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

interface Scene extends ConversationObject
{
    public function getTurns(): TurnCollection;

    public function setTurns(TurnCollection $conversations);

    public function getConversation(): Conversation;
}
