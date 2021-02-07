<?php
namespace OpenDialogAi\Core\Conversation;

class Conversation extends ConversationObject
{
    protected SceneCollection $scenes;
    protected Scenario $scenario;

    public function __construct(Scenario $scenario)
    {
        parent::__construct();
        $this->scenario = $scenario;
        $this->scenes = new SceneCollection();
    }

    public function hasScenes():bool
    {
        if ($this->scenes->isNotEmpty()) return true;

        return false;
    }

    public function getScenes(): SceneCollection
    {
        return $this->scenes;
    }

    public function setScenes(SceneCollection $scenes)
    {
        $this->scenes = $scenes;
    }

    public function getScenario(): Scenario
    {
        return $this->scenario;
    }
}
