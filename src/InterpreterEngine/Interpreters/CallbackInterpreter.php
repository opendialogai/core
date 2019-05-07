<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\ButtonValueParser;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\ButtonResponseUtterance;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;

class CallbackInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.callbackInterpreter';

    /** @var AttributeResolver */
    private $attributeResolver;

    /**
     * @var array - the callbacks supported by the application.
     */
    private $supportedCallbacks = [];

    /**
     * @param AttributeResolver $attributeResolver
     */
    public function setAttributeResolver(AttributeResolver $attributeResolver): void
    {
        $this->attributeResolver = $attributeResolver;
    }

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

                if ($utterance->getType() === ButtonResponseUtterance::TYPE && $value = $utterance->getValue()) {
                    $intent->addAttribute($this->getButtonValueAttribute($value));
                }
            }
        } catch (FieldNotSupported $e) {
            Log::warning(sprintf('Utterance %s does not support callbacks', $utterance->getType()));
        }

        return [$intent];
    }

    /**
     * @param string $value
     * @return AttributeInterface
     */
    protected function getButtonValueAttribute(string $value): AttributeInterface
    {
        $parsed = ButtonValueParser::parseButtonValue($value);

        try {
            $attribute = $this->attributeResolver->getAttributeFor(
                $parsed[ButtonValueParser::ATTRIBUTE_NAME],
                $parsed[ButtonValueParser::ATTRIBUTE_VALUE]
            );
        } catch (AttributeDoesNotExistException $e) {
            $attribute = new StringAttribute(
                $parsed[ButtonValueParser::ATTRIBUTE_NAME],
                $parsed[ButtonValueParser::ATTRIBUTE_VALUE]
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
