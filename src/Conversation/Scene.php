<?php
namespace OpenDialogAi\Core\Conversation;

class Scene extends ConversationObject
{
    protected TurnCollection $turns;
    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();
        $this->turns = new TurnCollection();
        $this->conversation = $conversation;
    }

    public function hasTurns(): bool
    {
        if ($this->turns->isNotEmpty()) return true;

        return false;
    }

    public function getTurns(): TurnCollection
    {
        return $this->turns;
    }

    public function setTurns(TurnCollection $turns)
    {
        $this->turns = $turns;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }
}
