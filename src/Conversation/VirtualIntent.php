<?php


namespace OpenDialogAi\Core\Conversation;


class VirtualIntent
{
    protected string $speaker;
    protected string $intentId;

    public function __construct(string $speaker, string $intentId)
    {
        $this->speaker = $speaker;
        $this->intentId = $intentId;
    }

    public function getSpeaker() {
        return $this->speaker;
    }

    public function getIntentId() {
        return $this->intentId;
    }

}
