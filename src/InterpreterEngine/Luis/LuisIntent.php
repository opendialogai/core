<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUIntent;

class LuisIntent extends AbstractNLUIntent
{
    public function __construct($intent)
    {
        $this->label = $intent->intent;
        $this->confidence = floatval($intent->score);
    }
}
