<?php

namespace OpenDialogAi\Core\Conversation;

class Scene extends ConversationObject
{
    public const CURRENT_SCENE = 'current_scene';
    public const TYPE = 'scene';
    public const CONVERSATION = 'conversation';
    public const TURNS = 'turns';

    protected ?TurnCollection $turns = null;
    protected ?Conversation $conversation = null;

    public function __construct(?Conversation $conversation = null)
    {
        parent::__construct();
        $this->conversation = $conversation;
    }

    public static function allFields()
    {
        return [...self::localFields(), ...self::foreignFields()];
    }

    public static function localFields()
    {
        return parent::allFields();
    }

    public static function foreignFields()
    {
        return [self::CONVERSATION, self::TURNS];
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
        if($this->turns === null) {
            $this->turns = new TurnCollection();
        }
        $this->turns->addObject($turn);
    }

    /**
     * Gets the current interpreter by checking the scene's interpreter, or searching for a default up the tree
     * A null value indicates 'not hydrated'
     * An '' value indicates 'none'
     * Any other value indicates an interpreter (E.g interpreter.core.callback)
     */
    public function getInterpreter(): ?string
    {
        if($this->interpreter === null) {
            return null;
        }
        if($this->interpreter === '' && $this->conversation !== null) {
            return $this->conversation->getInterpreter();
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
