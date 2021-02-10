<?php

namespace OpenDialogAi\Core\InterpreterEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatButtonResponseUtterance;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class CallbackInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $validCallback = ['valid' => 'valid'];
        $this->setSupportedCallbacks($validCallback);
    }

    public function testBinding()
    {
        /** @var InterpreterServiceInterface $interpreterService */
        $interpreterService = resolve(InterpreterServiceInterface::class);

        $this->assertInstanceOf(CallbackInterpreter::class, $interpreterService->getDefaultInterpreter());
    }

    /**
     */
    public function testUnmappedCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new UtteranceAttribute('unmapped');
        $utterance->setCallbackId('un-mapped');

        $this->assertEquals('un-mapped', $callbackInterpreter->interpret($utterance)->first()->getODId());
    }

    /**
=     */
    public function testValidCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new UtteranceAttribute('test_utterance');
        $utterance->setCallbackId('valid');

        $this->assertEquals('valid', $callbackInterpreter->interpret($utterance)[0]->getODId());
    }

    /**
     */
    public function testGetButtonValueNoAttributeName()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new UtteranceAttribute("badly_named");
        $utterance->setCallbackId('valid');
        $utterance->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA_VALUE, "not_sure");

        $intent = $callbackInterpreter->interpret($utterance)->first();
        $this->assertEquals('valid', $intent->getODId());
        $this->assertInstanceOf(StringAttribute::class, $intent->getAttributes()->first()->value);
        $this->assertEquals('callback_value', $intent->getAttributes()->first()->value->getId());
        $this->assertEquals('not_sure', $intent->getAttributes()->first()->value->getValue());
    }

    public function testGetButtonValueWithAttributeName()
    {
        $customAttribute = ['age' => IntAttribute::class];
        $this->setCustomAttributes($customAttribute);

        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new UtteranceAttribute('test_utterance');
        $utterance->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_BUTTON_RESPONSE);
        $utterance->setCallbackId('valid');
        $utterance->setCallbackValue('age.21');

        $intent = $callbackInterpreter->interpret($utterance)[0];

        $this->assertInstanceOf(IntAttribute::class, $intent->getAttributes()->first()->value);
        $this->assertEquals('age', $intent->getAttributes()->first()->value->getId());
        $this->assertEquals(21, $intent->getAttributes()->first()->value->getValue());
    }

    public function testCallbackInterpreterAttributeNotSet()
    {
        $utterance = new UtteranceAttribute('test_utterance');
        $utterance->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_BUTTON_RESPONSE);
        $utterance->setCallbackId('valid');
        $utterance->setCallbackValue('not_set.21');

        $intent = $this->getCallbackInterpreter()->interpret($utterance)[0];

        $this->assertCount(1, $intent->getAttributes());
        $this->assertEquals(StringAttribute::class, get_class($intent->getAttributes()->first()->value));
    }

    protected function getCallbackInterpreter(): InterpreterInterface
    {
        return resolve(InterpreterServiceInterface::class)->getDefaultInterpreter();
    }
}
