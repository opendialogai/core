<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUInterpreter;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;

class LuisInterpreter extends AbstractNLUInterpreter
{
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;
    protected static string $componentId = 'interpreter.core.luis';

    protected static $entityConfigKey = 'opendialog.interpreter_engine.luis_entities';

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(LuisClient::class);
    }
}
