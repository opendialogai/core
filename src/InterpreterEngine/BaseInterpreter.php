<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;

abstract class BaseInterpreter implements InterpreterInterface, OpenDialogComponent
{
    use ODComponent;

    protected static string $componentType = ODComponentTypes::INTERPRETER_COMPONENT_TYPE;
    protected static string $componentSource = ODComponentTypes::APP_COMPONENT_SOURCE;
}
