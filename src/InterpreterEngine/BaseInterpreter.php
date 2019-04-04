<?php

namespace OpenDialogAi\InterpreterEngine;

use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;

abstract class BaseInterpreter implements InterpreterInterface
{
    protected static $name = 'base';

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        if (static::$name === self::$name) {
            throw new InterpreterNameNotSetException(sprintf("Interpreter %s has not defined a name", __CLASS__));
        }
        return static::$name;
    }
}
