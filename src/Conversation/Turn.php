<?php
namespace OpenDialogAi\Core\Conversation;

class Turn extends ConversationObject
{
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

    public function getScene(): Scene
    {
        return $this->scene;
    }

    public function addRequestIntent(Intent $intent)
    {
        $this->requestIntents->addObject($intent);
    }

    public function getRequestIntent(string $odId): ?Intent
    {
        return $this->requestIntents->getObject($odId);
    }

    public function addResponseIntent(Intent $intent)
    {
        $this->responseIntents->addObject($intent);
    }

    public function getResponseIntent(string $odId): ?Intent
    {
        return $this->responseIntents->getObject($odId);
    }

}
