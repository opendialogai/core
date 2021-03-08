<?php

namespace OpenDialogAi\Core\Conversation;

use DateTime;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\Core\Conversation\Exceptions\InvalidSpeakerTypeException;

class Intent extends ConversationObject
{
    use HasAttributesTrait;

    public const USER = 'USER';
    public const APP = 'APP';

    public const CURRENT_INTENT = 'current_intent';
    public const TYPE = 'intent';
    public const INTERPRETED_INTENT = 'interpreted_intent';
    public const CURRENT_SPEAKER = 'speaker';

    public const TURN = 'turn';
    public const SPEAKER = 'speaker';
    public const CONFIDENCE = 'confidence';
    public const SAMPLE_UTTERANCE = 'sampleUtterance';
    public const TRANSITION = 'transition';
    public const LISTENS_FOR = 'listensFor';
    public const VIRTUAL_INTENTS = 'virtualIntents';
    public const EXPECTED_ATTRIBUTES = 'expectedAttributes';
    public const ACTIONS = 'actions';

    const VALID_SPEAKERS = [
        self::USER, self::APP,
    ];

    protected ?Turn $turn;
    protected string $speaker;
    protected float $confidence;
    protected string $sampleUtterance;
    protected ?Transition $transition;
    protected array $listensFor;
    protected VirtualIntentCollection $virtualIntents;
    protected array $expectedAttributes;
    protected ActionsCollection $actions;

    // The interpreted intents is a collection interpretations of this intent that are added through an interpreter.
    protected IntentCollection $interpretedIntents;
    protected Intent $interpretation;

    public static function localFields() {
        return array_merge(parent::localFields(), [self::SPEAKER, self::CONFIDENCE, self::SAMPLE_UTTERANCE, self::TRANSITION,
            self::LISTENS_FOR, self::VIRTUAL_INTENTS, self::EXPECTED_ATTRIBUTES, self::ACTIONS]);
    }

    public function __construct(string $uid, string $odId, string $name, ?string $description, ConditionCollection $conditions,
        BehaviorsCollection  $behaviors, ?string $interpreter, DateTime $createdAt, DateTime $updatedAt, string $speaker, float
        $confidence, string $sampleUtterance, ?Transition $transition, array $listensFor, VirtualIntentCollection $virtualIntents, array
        $expectedAttributes, ActionsCollection $actions)
    {
        parent::__construct($uid, $odId, $name, $description, $conditions, $behaviors, $interpreter, $createdAt, $updatedAt);
        $this->speaker = $speaker;
        $this->confidence = $confidence;
        $this->sampleUtterance = $sampleUtterance;
        $this->transition = $transition;
        $this->listensFor = $listensFor;
        $this->virtualIntents = $virtualIntents;
        $this->expectedAttributes = $expectedAttributes;
        $this->actions = $actions;
        $this->turn = null;
    }

    public static function createNoMatchIntent(): Intent
    {
        $intent = new self();
        $intent->setODId('intent.core.NoMatch');
        return $intent;
    }

    public static function createIntent($odId, $confidence): Intent
    {
        $intent = new self();
        $intent->setODId($odId);
        $intent->setConfidence($confidence);
        return $intent;
    }

    public function getSpeaker(): ?string
    {
        return $this->speaker;
    }

    public function setSpeaker(string $speaker)
    {
        if (!in_array($speaker, self::VALID_SPEAKERS)) {
            throw new InvalidSpeakerTypeException(sprintf('Speaker type %s is not found in valid speaker types.', $speaker));
        }
        $this->speaker = $speaker;
    }

    public function addInterpretedIntents(IntentCollection $interpretations)
    {
        $this->interpretedIntents = $this->interpretedIntents->concat($interpretations);
    }

    public function getInterpretedIntents(): IntentCollection
    {
        return $this->interpretedIntents;
    }

    /**
     * Goes through interpreted intents and looks for a match.
     *
     * @return bool
     */
    public function checkForMatch(): bool
    {
        /* @var Intent $intent */
        foreach ($this->interpretedIntents as $intent) {
            if (($intent->getODId() == $this->getODId()) && ($intent->getConfidence() >= $this->getConfidence())) {
                // @todo currently this will give us the last matching intent out of a number of intents as the "winner"
                // so we may want to consider ranking within a single intent as well if we got multiple matches.
                $this->interpretation = $intent;
                return true;
            }
        }
        return false;
    }

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence)
    {
        $this->confidence = $confidence;
    }

    /**
     * Returns the interpreted intent that was a match.
     *
     * @return Intent
     */
    public function getInterpretation(): Intent
    {
        return $this->interpretation;
    }

    /**
     * @return string|null
     */
    public function getInterpreter()
    {
        if (isset($this->interpreter)) {
            return $this->interpreter;
        }

        if (isset($this->turn)) {
            return $this->turn->getInterpreter();
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
     * @return Scene|null
     */
    public function getScene(): ?Scene
    {
        if ($this->getTurn() != null) {
            return $this->getTurn()->getScene();
        }
        return null;
    }

    public function getTurn(): ?Turn
    {
        return $this->turn;
    }

    public function setTurn(Turn $turn): void {
        $this->turn = $turn;
    }

    /**
     * @return ActionsCollection
     */
    public function getActions(): ActionsCollection
    {
        return $this->actions;
    }

    /**
     * @param  ActionsCollection  $actions
     *
     * @return Intent
     */
    public function setActions(ActionsCollection $actions): Intent
    {
        $this->actions = $actions;
        return $this;
    }

    public function getSampleUtterance(): ?string
    {
        return $this->sampleUtterance;
    }

    public function setSampleUtterance(string $sampleUtterance)
    {
        $this->sampleUtterance = $sampleUtterance;
    }

    public function getTransition(): ?Transition {
        return $this->transition;
    }

    public function setTransition(?Transition $transition): void {
        $this->transition = $transition;
    }

    public function getExpectedAttributes(): array {
        return $this->expectedAttributes;
    }

    public function setExpectedAttributes(array $expectedAttributes): void {
        $this->expectedAttributes = $expectedAttributes;
    }

    public function getListensFor(): array {
        return $this->listensFor;
    }

    public function setListensFor(array $listensFor): void {
        $this->listensFor = $listensFor;
    }

    public function getVirtualIntents(): VirtualIntentCollection {
        return $this->virtualIntents;
    }

    public function setVirtualIntents(VirtualIntentCollection $virtualIntents): void {
        $this->virtualIntents = $virtualIntents;
    }
}
