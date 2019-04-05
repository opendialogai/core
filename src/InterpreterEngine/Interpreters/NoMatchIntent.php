<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use OpenDialogAi\Core\Conversation\Intent;

/**
 * A default NoMatch intent to use when an interpreter cannot establish the intent of a message
 */
class NoMatchIntent extends Intent
{
    const NO_MATCH = "NO_MATCH";

    public function __construct()
    {
        parent::__construct(self::NO_MATCH);
        parent::setConfidence(1);
    }
}
