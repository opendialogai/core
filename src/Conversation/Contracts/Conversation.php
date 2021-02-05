<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

interface Conversation extends ConversationObject
{
    public function getScenes(): SceneCollection;

    public function setScenes(SceneCollection $conversations);

    public function getScenario(): Scenario;
}
