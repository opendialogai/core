<?php
namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use \DateTime;

class Conversation extends ConversationObject
{
    public const CURRENT_CONVERSATION = 'current_conversation';
    public const TYPE = 'conversation';
    public const SCENES = 'scenes';
    public const SCENARIO = 'scenario';

    protected SceneCollection $scenes;
    protected ?Scenario $scenario;


    public static function localFields() {
        return parent::localFields();
    }

    public function __construct(?Scenario $scenario = null)
    {
        parent::__construct();
        $this->scenario = $scenario;
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

    public function setScenario(Scenario $scenario): void {
        $this->scenario = $scenario;
    }

    public function addScene(Scene $scene)
    {
        if($this->scenes === null) {
            throw new InsufficientHydrationException("Field 'scenes' on Conversation has not been hydrated.");
        }
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
