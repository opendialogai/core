<?php
namespace OpenDialogAi\Core\Conversation;



use App\Http\Resources\ConversationCollection;

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
        if ($this->conversations->isEmpty()) return false;

        return true;
    }

    public function getConversations(): ConversationCollection
    {
        return $this->conversations;
    }
}
