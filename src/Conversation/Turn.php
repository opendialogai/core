<?php
namespace OpenDialogAi\Core\Conversation;

use DateTime;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;

class Turn extends ConversationObject
{
    public const CURRENT_TURN = 'current_turn';
    public const TYPE = 'turn';
    public const SCENE = 'scene';
    public const REQUEST_INTENTS = 'requestIntents';
    public const RESPONSE_INTENTS = 'responseIntents';
    public const VALID_ORIGINS = 'validOrigins';
    public const LOCAL_FIELDS = ConversationObject::LOCAL_FIELDS + [self::VALID_ORIGINS];

    protected ?Scene $scene;

    // The set of possible intents that could open a turn
    protected IntentCollection $requestIntents;

    // The set of possible intents that could provide a response
    protected IntentCollection $responseIntents;

    protected array $validOrigins;

    public function __construct(string $uid, string $odId, string $name, ?string $description, ConditionCollection $conditions,
        BehaviorsCollection  $behaviors, ?string $interpreter, DateTime $createdAt, DateTime $updatedAt, array $validOrigins)
    {
        parent::__construct($uid, $odId, $name, $description, $conditions, $behaviors, $interpreter, $createdAt, $updatedAt);
        $this->validOrigins = $validOrigins;
        $this->turns = new TurnCollection();
        $this->requestIntents = new IntentCollection();
        $this->responseIntents = new IntentCollection();
        $this->scene = null;

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

    public function setScene(Scene $scene): void {
        $this->scene = $scene;
    }

    public function addRequestIntent(Intent $intent)
    {
        if($this->requestIntents === null) {
            throw new InsufficientHydrationException("Field 'requestIntents' on Turn has not been hydrated.");
        }
        $this->requestIntents->addObject($intent);
    }

    public function addResponseIntent(Intent $intent)
    {
        if($this->responseIntents === null) {
            throw new InsufficientHydrationException("Field 'responseIntents' on Turn has not been hydrated.");
        }
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

    public function hasValidOrigins(): bool {
        return !empty($this->validOrigins);
    }

    public function getValidOrigins(): array {
        return $this->validOrigins;
    }

    public function setValidOrigins(array $validOrigins): void {
        $this->validOrigins = $validOrigins;
    }
}
