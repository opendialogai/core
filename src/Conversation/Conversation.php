<?php
namespace OpenDialogAi\Core\Conversation;

class Conversation extends ConversationObject
{
    public const CURRENT_CONVERSATION = 'current_conversation';


    protected SceneCollection $scenes;
    protected Scenario $scenario;

    public function __construct(?Scenario $scenario = null)
    {
        parent::__construct();
        isset($scenario) ? $this->scenario = $scenario : null;
        $this->scenes = new SceneCollection();
    }

    public function hasScenes():bool
    {
        return $this->scenes->isNotEmpty();
    }

    public function getScenes(): SceneCollection
    {
        return $this->scenes;
    }

    public function setScenes(SceneCollection $scenes)
    {
        $this->scenes = $scenes;
    }

    public function getScenario(): ?Scenario
    {
        return $this->scenario;
    }

    public function addScene(Scene $scene)
    {
        $this->scenes->addObject($scene);
    }

    /**
     * @return string|null
     */
    public function getInterpreter()
    {
        if (isset($this->interpreter)) {
            return $this->interpreter;
        }

        if (isset($this->scenario)) {
            return $this->scenario->getInterpreter();
        }
        return null;
    }
}
