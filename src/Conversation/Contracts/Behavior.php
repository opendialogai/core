<?php


namespace OpenDialogAi\Core\Conversation\Contracts;

interface Behavior
{
    public function getBehavior(): string;

    public function setBehavior(string $behavior);
}
