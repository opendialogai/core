<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\CallbackValueParser;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class CallbackInterpreter extends BaseInterpreter
{
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;
    protected static ?string $componentId = 'interpreter.core.callbackInterpreter';

    protected static ?string $componentName = 'Callback';
    protected static ?string $componentDescription = 'An interpreter for directly matching intent names.';

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
    public function interpret(UtteranceAttribute $utterance): IntentCollection
    {
        $intent = Intent::createNoMatchIntent();

        if ($utterance->getCallbackId()) {
            $intentName = $utterance->getCallbackId();

            if (array_key_exists($intentName, $this->supportedCallbacks)) {
                $intentName = $this->supportedCallbacks[$utterance->getCallbackId()];
            }
            $intent = Intent::createIntent($intentName, 1);

            $this->setValue($utterance, $intent);
            $this->setFormValues($utterance, $intent);
        }

        $collection = new IntentCollection();
        $collection->add($intent);
        return $collection;
    }

    /**
     * @param string $value
     * @return Attribute
     */
    protected function getCallbackValueAttribute(string $value): ?Attribute
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
     * @param UtteranceAttribute $utterance
     * @param Intent $intent
     */
    public function setValue(UtteranceAttribute $utterance, Intent $intent): void
    {
        if ($utterance->getCallbackValue() && $this->getCallbackValueAttribute($utterance->getCallbackValue())) {
            $intent->addAttribute($this->getCallbackValueAttribute($utterance->getCallbackValue()));
        }
    }

    /**
     * @param UtteranceAttribute $utterance
     * @param Intent $intent
     */
    public function setFormValues(UtteranceAttribute $utterance, Intent $intent): void
    {
        if ($utterance->getFormValues()) {
            foreach ($utterance->getFormValues() as $name => $value) {
                $intent->addAttribute(AttributeResolver::getAttributeFor($name, $value));
            }
        }
    }
}
