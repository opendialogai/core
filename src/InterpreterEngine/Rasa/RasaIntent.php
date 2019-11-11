<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUIntent;

class RasaIntent extends AbstractNLUIntent
{
    public function __construct($intent)
    {
        $this->label = $intent->name;
        $this->confidence = floatval($intent->confidence);
    }
}
