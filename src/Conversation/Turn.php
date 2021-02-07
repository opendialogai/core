<?php
namespace OpenDialogAi\Core\Conversation;

class Turn extends ConversationObject
{
    protected IntentCollection $intents;
    protected Scene $scene;

    public function __construct(Scene $scene)
    {
        parent::__construct();
        $this->intents = new IntentCollection();
        $this->scene = $scene;
    }

    public function hasIntents(): bool
    {
        if ($this->intents->isNotEmpty()) return true;

        return false;
    }

    public function getIntents(): IntentCollection
    {
        return $this->intents;
    }

    public function setIntents(IntentCollection $intents)
    {
        $this->intents = $intents;
    }

    public function getScene(): Scene
    {
        return $this->scene;
    }
}
