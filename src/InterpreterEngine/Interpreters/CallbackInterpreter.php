<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\AttributeEngine\CallbackValueParser;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
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
        if ($utterance->getCallbackId()) {
            $intentName = $utterance->getCallbackId();

            if (array_key_exists($intentName, $this->supportedCallbacks)) {
                $intentName = $this->supportedCallbacks[$utterance->getCallbackId()];
            }

            $intent = new Intent($intentName);
            $intent->setConfidence(1);

            $this->setValue($utterance, $intent);
            $this->setFormValues($utterance, $intent);
        }

        return [$intent];
    }

    /**
     * @param string $value
     * @return AttributeInterface
     */
    protected function getCallbackValueAttribute(string $value): ?AttributeInterface
    {
        $parsed = CallbackValueParser::parseCallbackValue($value);

        $attribute = AttributeResolver::getAttributeFor(
            $parsed[CallbackValueParser::ATTRIBUTE_NAME],
            $parsed[CallbackValueParser::ATTRIBUTE_VALUE]
        );

        Log::debug(sprintf(
            'Adding attribute %s with value %s to intent.',
            $attribute->getId(),
            $attribute->getValue()
        ));

        return $attribute;
    }

    /**
     * @param UtteranceInterface $utterance
     * @param Intent $intent
     */
    public function setValue(UtteranceInterface $utterance, Intent $intent): void
    {
        try {
            if ($utterance->getValue() && $this->getCallbackValueAttribute($utterance->getValue())) {
                $intent->addAttribute($this->getCallbackValueAttribute($utterance->getValue()));
            }
        } catch (FieldNotSupported $e) {
            Log::debug(
                sprintf(
                    'Callback interpreter trying to extract value from unsupported utterance %s',
                    get_class($utterance)
                )
            );
        }
    }

    /**
     * @param UtteranceInterface $utterance
     * @param Intent $intent
     */
    public function setFormValues(UtteranceInterface $utterance, Intent $intent): void
    {
        try {
            if ($utterance->getFormValues()) {
                foreach ($utterance->getFormValues() as $name => $value) {
                    $intent->addAttribute(AttributeResolver::getAttributeFor($name, $value));
                }
            }
        } catch (FieldNotSupported $e) {
            Log::debug(
                sprintf(
                    'Callback interpreter trying to extract form values from unsupported utterance %s',
                    get_class($utterance)
                )
            );
        }
    }
}
