<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUInterpreter;
use OpenDialogAi\InterpreterEngine\Rasa\RasaClient;

class RasaInterpreter extends AbstractNLUInterpreter
{
    protected static $name = 'interpreter.core.rasa';

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(RasaClient::class);
    }

}
