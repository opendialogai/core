<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class CallbackInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.callbackInterpreter';

    private $supportedCallbacks;

    public function setSupportedCallbacks($supportedCallbacks)
    {
        $this->supportedCallbacks = $supportedCallbacks;
    }

    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            if (array_key_exists($utterance->getCallbackId(), $this->supportedCallbacks)) {
                $intent = new Intent($this->supportedCallbacks[$utterance->getCallbackId()]);
                $intent->setConfidence(1);
                return [$intent];
            }
        } catch (FieldNotSupported $e) {
            Log::warning(sprintf('Utterance %s does not support callbacks', $utterance->getType()));
        }
    }
}
