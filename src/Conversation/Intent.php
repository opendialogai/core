<?php
namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Conversation\Exceptions\InvalidSpeakerTypeException;

class Intent extends ConversationObject
{
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

    public function __construct(?Turn $turn = null, ?string $speaker = null)
    {
        parent::__construct();
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
}
