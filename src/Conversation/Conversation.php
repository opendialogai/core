<?php

namespace OpenDialogAi\Core\Conversation;


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
        return [self::SCENARIO, self::SCENES];
    }

    public function hasScenes(): bool
    {
        return $this->scenes !== null && $this->scenes->isNotEmpty();
    }

    public function getScenes(): ?SceneCollection
    {
        return $this->scenes;
    }

    public function setScenes(SceneCollection $scenes)
    {
        $this->scenes = $scenes;
    }

    public function addScene(Scene $scene)
    {
        if($this->scenes === null) {
            $this->scenes = new SceneCollection();
        }
        $this->getScenes()->addObject($scene);
    }

    /**
     * Gets the current interpreter by checking the conversations interpreter, or searching for a default up the tree
     * A null value indicates 'not hydrated'
     * An '' value indicates 'none'
     * Any other value indicates an interpreter (E.g interpreter.core.callback)
     */
    public function getInterpreter(): ?string
    {
        if($this->interpreter === null) {
            return null;
        }
        if($this->interpreter === '' && $this->scenario !== null) {
            return $this->scenario->getInterpreter();
        }
        return $this->interpreter;
    }

    public function getScenario(): ?Scenario
    {
        return $this->scenario;
    }

    public function setScenario(Scenario $scenario): void
    {
        $this->scenario = $scenario;
    }
}
