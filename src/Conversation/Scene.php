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
        $this->conversation = $conversation;
    }

    public static function localFields()
    {
        return parent::allFields();
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
     * Gets the current interpreter by checking the scene's interpreter, or searching for a default up the tree
     * A null value indicates 'not hydrated'
     * An '' value indicates 'none'
     * Any other value indicates an interpreter (E.g interpreter.core.callback)
     */
    public function getInterpreter(): string
    {
        if ($this->interpreter === null) {
            throw new InsufficientHydrationException("Interpreter on Scene has not been hydrated.");
        }
        if ($this->interpreter === '') {
            return $this->getConversation()->getInterpreter();
        }
        return $this->interpreter;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): void
    {
        $this->conversation = $conversation;
    }

    public function getScenario(): ?Scenario
    {
        if ($this->getConversation() != null) {
            return $this->getConversation()->getScenario();
        }

        return null;
    }
}
