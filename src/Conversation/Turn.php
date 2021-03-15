<?php

namespace OpenDialogAi\Core\Conversation;

class Turn extends ConversationObject
{
    public const CURRENT_TURN = 'current_turn';
    public const TYPE = 'turn';
    public const SCENE = 'scene';
    public const REQUEST_INTENTS = 'requestIntents';
    public const RESPONSE_INTENTS = 'responseIntents';
    public const VALID_ORIGINS = 'validOrigins';
    public const ORDER_REQUEST = "REQUEST";
    public const ORDER_RESPONSE = "RESPONSE";

    protected ?Scene $scene = null;

    // The set of possible intents that could open a turn
    protected ?IntentCollection $requestIntents = null;

    // The set of possible intents that could provide a response
    protected ?IntentCollection $responseIntents = null;

    protected ?TurnCollection $validOrigins = null;

    public function __construct(?Scene $scene = null)
    {
        parent::__construct();
        $this->scene = $scene;
    }

    public static function foreignFields()
    {
        return [...parent::foreignFields(), self::SCENE, self::VALID_ORIGINS, self::REQUEST_INTENTS, self::RESPONSE_INTENTS];
    }

    public function hasRequestIntents(): bool
    {
        return $this->requestIntents !== null && $this->requestIntents->isNotEmpty();
    }

    public function hasResponseIntents(): bool
    {
        return $this->responseIntents !== null && $this->responseIntents->isNotEmpty();
    }

    public function getRequestIntents(): ?IntentCollection
    {
        return $this->requestIntents;
    }

    public function setRequestIntents(IntentCollection $intents)
    {
        $this->requestIntents = $intents;
    }

    public function getResponseIntents(): ?IntentCollection
    {
        return $this->responseIntents;
    }

    public function setResponseIntents(IntentCollection $intents)
    {
        $this->responseIntents = $intents;
    }

    public function addRequestIntent(Intent $intent)
    {
        if($this->requestIntents === null) {
            $this->requestIntents = new IntentCollection();
        }
        $this->requestIntents->addObject($intent);
    }

    public function addResponseIntent(Intent $intent)
    {
        if($this->responseIntents === null) {
            $this->responseIntents = new IntentCollection();
        }
        $this->responseIntents->addObject($intent);
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
        if($this->interpreter === '' && $this->scene !== null) {
            return $this->scene->getInterpreter();
        }
        return $this->interpreter;
    }

    public function getScene(): ?Scene
    {
        return $this->scene;
    }

    public function setScene(Scene $scene): void
    {
        $this->scene = $scene;
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

    public function hasValidOrigins(): bool
    {
        return !empty($this->validOrigins);
    }

    public function getValidOrigins(): ?TurnCollection
    {
        return $this->validOrigins;
    }

    public function setValidOrigins(TurnCollection $validOrigins): void
    {
        $this->validOrigins = $validOrigins;
    }
}
