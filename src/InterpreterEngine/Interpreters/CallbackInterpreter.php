<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\CallbackValueParser;
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
        $intent = new NoMatchIntent();
        try {
            $callbackId = $utterance->getCallbackId();
            if (isset($callbackId) && array_key_exists($callbackId, $this->supportedCallbacks)) {
                $intent = new Intent($this->supportedCallbacks[$utterance->getCallbackId()]);
                $intent->setConfidence(1);

                if ($utterance->getValue()) {
                    $intent->addAttribute($this->getCallbackValueAttribute($utterance->getValue()));
                }
            }
        } catch (FieldNotSupported $e) {
            Log::warning(sprintf('Utterance %s does not support callbacks or callback values', $utterance->getType()));
        }

        return [$intent];
    }

    /**
     * @param string $value
     * @return AttributeInterface
     */
    protected function getCallbackValueAttribute(string $value): AttributeInterface
    {
        $parsed = CallbackValueParser::parseCallbackValue($value);

        try {
            $attribute = AttributeResolver::getAttributeFor(
                $parsed[CallbackValueParser::ATTRIBUTE_NAME],
                $parsed[CallbackValueParser::ATTRIBUTE_VALUE]
            );
        } catch (AttributeIsNotSupported $e) {
            $attribute = new StringAttribute(
                $parsed[CallbackValueParser::ATTRIBUTE_NAME],
                $parsed[CallbackValueParser::ATTRIBUTE_VALUE]
            );
        }

        Log::debug(sprintf(
            'Adding attribute %s with value %s to intent.',
            $attribute->getId(),
            $attribute->getValue()
        ));

        return $attribute;
    }
}
