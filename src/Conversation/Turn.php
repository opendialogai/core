<?php
namespace OpenDialogAi\Core\Conversation;

class Turn extends ConversationObject
{
    public const CURRENT_TURN = 'current_turn';

    // The set of possible intents that could open a turn
    protected IntentCollection $requestIntents;

    // The set of possible intents that could provide a response
    protected IntentCollection $responseIntents;

    protected Scene $scene;

    public function __construct(?Scene $scene = null)
    {
        parent::__construct();
        $this->requestIntents = new IntentCollection();
        $this->responseIntents = new IntentCollection();
        isset($scene) ? $this->scene = $scene :null ;
    }

    public function hasRequestIntents(): bool
    {
        return $this->requestIntents->isNotEmpty();
    }

    public function hasResponseIntents(): bool
    {
        return $this->responseIntents->isNotEmpty();
    }

    public function getRequestIntents(): IntentCollection
    {
        return $this->requestIntents;
    }

    public function getResponseIntents(): IntentCollection
    {
        return $this->responseIntents;
    }

    public function setRequestIntents(IntentCollection $intents)
    {
        $this->intents = $intents;
    }

    public function setResponseIntents(IntentCollection $intents)
    {
        $this->intents = $intents;
    }

    public function getScene(): ?Scene
    {
        return $this->scene;
    }

    public function addRequestIntent(Intent $intent)
    {
        $this->requestIntents->addObject($intent);
    }

    public function addResponseIntent(Intent $intent)
    {
        $this->responseIntents->addObject($intent);
    }

    /**
     * @return string|null
     */
    public function getInterpreter()
    {
        if (isset($this->interpreter)) {
            return $this->interpreter;
        }

        if (isset($this->scene)) {
            return $this->scene->getInterpreter();
        }

        return null;
    }

    /**
     * @return Conversation|null
     */
    public function getConversation(): ?Conversation
    {
        if ($this->getScene() != null) {
            return $this->getScene()->getConversation();
        }

        return null;
    }

    /**
     * @return Scenario|null
     */
    public function getScenario(): ?Scenario
    {
        if ($this->getConversation() != null) {
            return $this->getConversation()->getScenario();
        }

        return null;
    }
}
