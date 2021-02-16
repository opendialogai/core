<?php
namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\Core\Conversation\Exceptions\InvalidSpeakerTypeException;

class Intent extends ConversationObject
{
    use HasAttributesTrait;

    public const USER = 'EI_USER';
    public const APP = 'EI_APP';
    public const HUMAN_AGENT = 'EI_HUMAN_AGENT';

    public const CURRENT_INTENT = 'current_intent';
    public const INTERPRETED_INTENT = 'interpreted_intent';
    public const CURRENT_SPEAKER = 'speaker';

    const VALID_SPEAKERS = [
        self::USER,
        self::APP,
        self::HUMAN_AGENT
    ];

    protected ?Turn $turn;
    protected ?string $speaker;
    protected ?float $confidence;

    // The interpreted intents is a collection interpretations of this intent that are added through an interpreter.
    protected IntentCollection $interpretedIntents;
    protected Intent $interpretation;

    public function __construct(?Turn $turn = null, ?string $speaker = null, ?string $interpreter = null)
    {
        parent::__construct();
        // Attributes hold entities that may be associated with this intent following interpretation
        $this->attributes = new Map();
        isset($turn) ? $this->turn = $turn : $this->turn = null;
        isset($speaker) ? $this->setSpeaker($speaker) : $this->speaker = null;
        isset($interpreter) ? $this->$interpreter = $interpreter : $this->interpreter = null;
        $this->interpretedIntents = new IntentCollection();
    }

    public function getTurn(): ?Turn
    {
        return $this->turn;
    }

    public function setSpeaker(string $speaker)
    {
        if (!in_array($speaker, self::VALID_SPEAKERS)) {
            throw new InvalidSpeakerTypeException(
                sprintf('Speaker type %s is not found in valid speaker types.', $speaker)
            );
        }
        $this->speaker = $speaker;
    }

    public function getSpeaker(): string
    {
        return $this->speaker;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence)
    {
        $this->confidence = $confidence;
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
     * Goes through intepreted intents and looks for a match.
     * @return bool
     */
    public function checkForMatch():bool
    {
        /* @var Intent $intent */
        foreach ($this->interpretedIntents as $intent) {
            if (($intent->getODId() == $this->getODId()) && ($intent->getConfidence() >= $this->getConfidence())) {
                $this->interpretation = $intent;
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the interpreted intent that was a match.
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
     * @return Scene|null
     */
    public function getScene(): ?Scene
    {
        if ($this->getTurn() != null) {
            return $this->getTurn()->getScene();
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
}
