<?php
namespace OpenDialogAi\InterpreterEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

/**
 * @method static IntentCollection interpret(string $interpreterName, UtteranceAttribute $utterance)
 * @method static IntentCollection interpretDefaultInterpreter(UtteranceAttribute $utterance)
 */
class InterpreterService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InterpreterServiceInterface::class;
    }
}
