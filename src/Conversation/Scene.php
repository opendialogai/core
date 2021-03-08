<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;

class Scene extends ConversationObject
{
    public const CURRENT_SCENE = 'current_scene';
    public const TYPE = 'scene';
    public const CONVERSATION = 'conversation';
    public const TURNS = 'turns';

    protected TurnCollection $turns;
    protected ?Conversation $conversation;

    public function __construct(?Conversation $conversation = null)
    {
        parent::__construct();
        $this->turns = new TurnCollection();
        $this->conversation = $conversation;
    }

    public static function localFields()
    {
        return parent::localFields();
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

    public function addTurn(Turn $turn)
    {
        if ($this->turns === null) {
            throw new InsufficientHydrationException("Field 'turns' on Scene has not been hydrated.");
        }
        $this->turns->addObject($turn);
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
            return $this->getConversation()->getScenario();
        }

        return null;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }
}
