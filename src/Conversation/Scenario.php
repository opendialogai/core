<?php

namespace OpenDialogAi\Core\Conversation;

class Scenario extends ConversationObject
{
    public const CURRENT_SCENARIO = 'current_scenario';
    public const TYPE = 'scenario';
    public const CONVERSATIONS = 'conversations';
    public const ACTIVE = 'active';
    public const STATUS = 'status';
    public const DRAFT_STATUS = "DRAFT";
    public const PREVIEW_STATUS = "PREVIEW";
    public const LIVE_STATUS = "LIVE";

    protected ?bool $active = null;
    protected ?string $status = null;
    protected ?ConversationCollection $conversations = null;

    public function __construct()
    {
        parent::__construct();
    }

    public static function localFields()
    {
        return [...parent::localFields(), self::ACTIVE, self::STATUS];
    }

    public static function foreignFields()
    {
        return [...parent::foreignFields(),self::CONVERSATIONS];
    }

    public function hasConversations(): bool
    {
        return $this->conversations !== null && $this->conversations->isNotEmpty();
    }

    public function addConversation(Conversation $conversation)
    {
        if($this->conversations === null) {
            $this->conversations = new ConversationCollection();
        }
        $this->conversations->addObject($conversation);
        $conversation->setScenario($this);
    }

    public function getConversations(): ?ConversationCollection
    {
        return $this->conversations;
    }

    public function setConversations(ConversationCollection $conversations)
    {
        $this->conversations = $conversations;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * Gets the status of the Scenario
     * A null value indicates 'not hydrated'
     * Any other values indicate a status (E.g 'DRAFT')
     *
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param $value
     */
    public function setStatus($value)
    {
        $this->status = $value;
    }

    /**
     * Checks if the scenario is active
     * A null value indicates 'not hydrated'
     * Any other value indicates a set value for 'active'
     *
     * @return bool
     */
    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function activate(): bool
    {
        $this->active = true;
        return $this->active;
    }

    /**
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->active = false;
        return $this->active;
    }
}
