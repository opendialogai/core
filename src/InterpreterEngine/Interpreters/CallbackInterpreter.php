<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class CallbackInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.callbackInterpreter';

    /**
     * @var array - the callbacks supported by the application.
     */
    private $supportedCallbacks = [];

    /**
     * @param $supportedCallbacks
     */
    public function setSupportedCallbacks($supportedCallbacks)
    {
        $this->supportedCallbacks = $supportedCallbacks;
    }

    /**
     * @param $callbackId
     * @param $intent
     */
    public function addCallback($callbackId, $intent)
    {
        $this->supportedCallbacks[$callbackId] = $intent;
    }

    /**
     * @inheritDoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $callbackId = $utterance->getCallbackId();
            if (isset($callbackId)) {
                if (array_key_exists($callbackId, $this->supportedCallbacks)) {
                    $intent = new Intent($this->supportedCallbacks[$utterance->getCallbackId()]);
                    $intent->setConfidence(1);

                    if ($value = $utterance->getValue()) {
                        $attribute = new StringAttribute('button_value', $value);
                        Log::debug(sprintf(
                            'Adding attribute %s with value %s to intent.',
                            $attribute->getId(),
                            $attribute->getValue()
                        ));
                        $intent->addAttribute($attribute);
                    }
                    return [$intent];
                }
            }
        } catch (FieldNotSupported $e) {
            Log::warning(sprintf('Utterance %s does not support callbacks', $utterance->getType()));
        }

        return [new NoMatchIntent()];
    }
}
