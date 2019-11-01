<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;

class LuisInterpreter extends AbstractNLUInterpreter
{
    protected static $name = 'interpreter.core.luis';

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(LuisClient::class);
    }

}
