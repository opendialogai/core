<?php
namespace OpenDialogAi\InterpreterEngine\Facades;

use Illuminate\Support\Facades\Facade;

class InterpreterService extends Facade
{
    protected static function getFacadeAccessor()
    {
        /**
         * @method static function interpret(string $interpreterName, UtteranceAttribute $utterance): IntentCollection
         * @method static function interpretDefaultInterpreter(UtteranceAttribute $utterance): IntentCollection
         */
        return \OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface::class;
    }
}
