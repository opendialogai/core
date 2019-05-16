<?php

namespace OpenDialogAi\Core\InterpreterEngine\Tests;

use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatButtonResponseUtterance;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class CallbackInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $validCallback = ['valid' => 'valid'];
        $this->setConfigValue('opendialog.interpreter_engine.supported_callbacks', $validCallback);
    }

    public function testBinding()
    {
        /** @var InterpreterServiceInterface $interpreterService */
        $interpreterService = app()->make(InterpreterServiceInterface::class);

        $this->assertInstanceOf(CallbackInterpreter::class, $interpreterService->getDefaultInterpreter());
    }

    public function testInvalidCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('invalid');

        $this->assertEquals(NoMatchIntent::NO_MATCH, $callbackInterpreter->interpret($utterance)[0]->getId());
    }

    public function testValidCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');

        $this->assertEquals('valid', $callbackInterpreter->interpret($utterance)[0]->getId());
    }

    public function testGetButtonValueNoAttributeName()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');
        $utterance->setValue('badly_named');

        $intent = $callbackInterpreter->interpret($utterance)[0];

        $this->assertEquals('valid', $intent->getId());
        $this->assertCount(1, $intent->getNonCoreAttributes());
        $this->assertInstanceOf(StringAttribute::class, $intent->getNonCoreAttributes()->first()->value);
        $this->assertEquals('callback_value', $intent->getNonCoreAttributes()->first()->value->getId());
        $this->assertEquals('badly_named', $intent->getNonCoreAttributes()->first()->value->getValue());
    }

    public function testGetButtonValueWithAttributeName()
    {
        $customAttribute = ['age' => IntAttribute::class];
        $this->setConfigValue('opendialog.context_engine.custom_attributes', $customAttribute);

        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');
        $utterance->setValue('age.21');

        $intent = $callbackInterpreter->interpret($utterance)[0];

        $this->assertInstanceOf(IntAttribute::class, $intent->getNonCoreAttributes()->first()->value);
        $this->assertEquals('age', $intent->getNonCoreAttributes()->first()->value->getId());
        $this->assertEquals(21, $intent->getNonCoreAttributes()->first()->value->getValue());
    }

    protected function getCallbackInterpreter(): InterpreterInterface
    {
        return app()->make(InterpreterServiceInterface::class)->getDefaultInterpreter();
    }
}

