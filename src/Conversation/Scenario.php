<?php
namespace OpenDialogAi\Core\Conversation;


class Scenario extends ConversationObject
{
    protected ConversationCollection $conversations;

    public function __construct()
    {
        parent::__construct();
        $this->conversations = new ConversationCollection();
    }

    public function hasConversations(): bool
    {
        return $this->conversations->isNotEmpty();
    }

    public function getConversations(): ConversationCollection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation)
    {
        $this->conversations->addObject($conversation);
    }

    public function getConversation(string $odId): ?Conversation
    {
        return $this->conversations->getObject($odId);
    }
}
