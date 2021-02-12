<?php
namespace OpenDialogAi\Core\Conversation;

class Scene extends ConversationObject
{
    public const CURRENT_SCENE = 'current_scene';

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

    public function getConversation(): ?Conversation
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

    /**
     * @return string|null
     */
    public function getInterpreter()
    {
        if (isset($this->interpreter)) {
            return $this->interpreter;
        }

        if (isset($this->conversation)) {
            return $this->conversation->getInterpreter();
        }
        return null;
    }

    public function getScenario(): ?Scenario
    {
        if ($this->getConversation() != null) {
            $this->getConversation()->getScenario();
        }

        return null;
    }
}
