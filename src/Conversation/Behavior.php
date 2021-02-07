<?php

namespace OpenDialogAi\Core\Conversation;

class Behavior
{
    protected string $behaviour;

    public function __construct(string $behavior)
    {
        $this->behaviour = $behavior;
    }

    public function getBehavior(): string
    {
        return $this->behaviour;
    }

    public function setBehavior(string $behavior)
    {
        $this->behaviour = $behavior;
    }
}
