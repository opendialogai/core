<?php
namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\Core\Conversation\Exceptions\InvalidSpeakerTypeException;

class Intent extends ConversationObject
{
    use HasAttributesTrait;

    public const USER = 'EI_USER';
    public const APP = 'EI_APP';
    public const HUMAN_AGENT = 'EI_HUMAN_AGENT';

    const VALID_SPEAKERS = [
        self::USER,
        self::APP,
        self::HUMAN_AGENT
    ];

    protected ?Turn $turn;
    protected ?string $speaker;
    protected ?float $confidence;

    public function __construct(?Turn $turn = null, ?string $speaker = null)
    {
        parent::__construct();
        // Attributes hold entities that may be associated with this intent following interpretation
        $this->attributes = new Map();
        isset($turn) ? $this->turn = $turn : null;
        isset($speaker) ? $this->setSpeaker($speaker): null;
    }

    public function getTurn(): Turn
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

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence)
    {
        $this->confidence = $confidence;
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
