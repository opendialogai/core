<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

/**
 * A default NoMatch intent to use when an interpreter cannot establish the intent of a message
 */
class NoMatchIntent
{
    const NO_MATCH = "intent.core.NoMatch";

    public function __construct()
    {
    }
}
