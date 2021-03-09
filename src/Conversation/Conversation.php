<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;

class Conversation extends ConversationObject
{
    public const CURRENT_CONVERSATION = 'current_conversation';
    public const TYPE = 'conversation';
    public const SCENES = 'scenes';
    public const SCENARIO = 'scenario';

    protected ?SceneCollection $scenes = null;
    protected ?Scenario $scenario = null;

    public function __construct(?Scenario $scenario = null)
    {
        parent::__construct();
        $this->scenario = $scenario;
        $this->scenes = new SceneCollection();
    }

    public static function localFields()
    {
        return parent::localFields();
    }

    public function hasScenes(): bool
    {
        return $this->getScenes()->isNotEmpty();
    }

    public function getScenes(): SceneCollection
    {
        if ($this->scenes === null) {
            throw new InsufficientHydrationException("Field 'scenes' on Conversation has not been hydrated.");
        }
        return $this->scenes;
    }

    public function setScenes(SceneCollection $scenes)
    {
        $this->scenes = $scenes;
    }

    public function getScenario(): Scenario
    {
        if ($this->scenario === null) {
            throw new InsufficientHydrationException("Field 'scenario' on Conversation has not been hydrated.");
        }
        return $this->scenario;
    }

    public function setScenario(Scenario $scenario): void
    {
        $this->scenario = $scenario;
    }

    public function addScene(Scene $scene)
    {
        $this->getScenes()->addObject($scene);
    }


    /**
     * Gets the current interpreter by checking the conversations interpreter, or searching for a default up the tree
     * A null value indicates 'not hydrated'
     * An '' value indicates 'none'
     * Any other value indicates an interpreter (E.g interpreter.core.callback)
     */
    public function getInterpreter(): string
    {
        if($this->interpreter === null) {
            throw new InsufficientHydrationException("Interpreter on Conversation has not been hydrated.");
        }
        if($this->interpreter === '') {
            return $this->getScenario()->getInterpreter();
        }
        return $this->interpreter;
    }
}
