<?php

namespace OpenDialogAi\Core\Conversation;

class Behavior
{
    const BEHAVIOR = 'behavior';
    const FIELDS = [self::BEHAVIOR];
    const STARTING = "STARTING";
    const OPEN = "OPEN";
    const COMPLETING = "COMPLETING";
    protected string $behavior;

    public function __construct(string $behavior)
    {
        $this->behavior = $behavior;
    }

    public function getBehavior(): string
    {
        return $this->behavior;
    }

    public function setBehavior(string $behavior)
    {
        $this->behavior = $behavior;
    }
}
