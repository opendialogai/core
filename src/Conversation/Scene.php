<?php
namespace OpenDialogAi\Core\Conversation;

class Scene extends ConversationObject
{
    protected TurnCollection $turns;
    protected Conversation $conversation;

    public function __construct(?Conversation $conversation = null)
    {
        parent::__construct();
        $this->turns = new TurnCollection();
        isset($conversation) ? $this->conversation = $conversation : null;
    }

    public function hasTurns(): bool
    {
        return $this->turns->isNotEmpty();
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

    public function addTurn(Turn $turn)
    {
        $this->turns->addObject($turn);
    }

    public function getTurn(string $odId): ?Turn
    {
        return $this->turns->getObject($odId);
    }
}
