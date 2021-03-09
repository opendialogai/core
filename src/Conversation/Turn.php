<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;

class Turn extends ConversationObject
{
    public const CURRENT_TURN = 'current_turn';
    public const TYPE = 'turn';
    public const SCENE = 'scene';
    public const REQUEST_INTENTS = 'requestIntents';
    public const RESPONSE_INTENTS = 'responseIntents';
    public const VALID_ORIGINS = 'validOrigins';

    protected ?Scene $scene;

    // The set of possible intents that could open a turn
    protected IntentCollection $requestIntents;

    // The set of possible intents that could provide a response
    protected IntentCollection $responseIntents;

    protected array $validOrigins;

    public function __construct(?Scene $scene = null)
    {
        parent::__construct();
        $this->requestIntents = new IntentCollection();
        $this->responseIntents = new IntentCollection();
        $this->scene = $scene;
    }

    public static function localFields()
    {
        return array_merge(parent::localFields(), self::VALID_ORIGINS);
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

    public function setRequestIntents(IntentCollection $intents)
    {
        $this->intents = $intents;
    }

    public function getResponseIntents(): IntentCollection
    {
        return $this->responseIntents;
    }

    public function setResponseIntents(IntentCollection $intents)
    {
        $this->intents = $intents;
    }

    public function addRequestIntent(Intent $intent)
    {
        if ($this->requestIntents === null) {
            throw new InsufficientHydrationException("Field 'requestIntents' on Turn has not been hydrated.");
        }
        $this->requestIntents->addObject($intent);
    }

    public function addResponseIntent(Intent $intent)
    {
        if ($this->responseIntents === null) {
            throw new InsufficientHydrationException("Field 'responseIntents' on Turn has not been hydrated.");
        }
        $this->responseIntents->addObject($intent);
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
            return $this->getScene()->getInterpreter();
        }
        return $this->interpreter;
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

    public function getScene(): ?Scene
    {
        return $this->scene;
    }

    public function setScene(Scene $scene): void
    {
        $this->scene = $scene;
    }

    public function hasValidOrigins(): bool
    {
        return !empty($this->validOrigins);
    }

    public function getValidOrigins(): array
    {
        return $this->validOrigins;
    }

    public function setValidOrigins(array $validOrigins): void
    {
        $this->validOrigins = $validOrigins;
    }
}
