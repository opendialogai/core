<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;

abstract class BaseInterpreter implements InterpreterInterface, OpenDialogComponent
{
    use ODComponent;
}
