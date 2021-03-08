<?php
namespace OpenDialogAi\Core\Conversation;

use DateTime;

class Scenario extends ConversationObject
{
    public const CURRENT_SCENARIO = 'current_scenario';
    public const TYPE = 'scenario';
    public const CONVERSATIONS = 'conversations';

    protected bool $active;
    protected string $status;
    protected ConversationCollection $conversations;

    public const ACTIVE = 'active';
    public const STATUS = 'status';

    public const DRAFT_STATUS = "DRAFT";
    public const PREVIEW_STATUS = "PREVIEW";
    public const LIVE_STATUS = "LIVE";

    public static function localFields() {
        return array_merge(parent::localFields(), [self::ACTIVE, self::STATUS]);
    }


    public function __construct()
    {
        parent::__construct();
        $this->conversations = new ConversationCollection();
    }

    public function hasConversations(): bool
    {
        return $this->conversations->isNotEmpty();
    }

    public function getConversations(): ConversationCollection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation)
    {
        $this->conversations->addObject($conversation);
        $conversation->setScenario($this);
    }

    /**
     * @return string|null
     */
    public function getInterpreter()
    {
        if (isset($this->interpreter)) {
            return $this->interpreter;
        }

        return null;
    }

    public function setActive(bool $active) {
        $this->active = $active;
    }
}
