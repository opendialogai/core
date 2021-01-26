<?php

namespace OpenDialogAi\Core\InterpreterEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
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
     * @throws FieldNotSupported
     */
    public function testUnmappedCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('un-mapped');

        $this->assertEquals('un-mapped', $callbackInterpreter->interpret($utterance)[0]->getId());
    }

    /**
     * @throws FieldNotSupported
     */
    public function testValidCallback()
    {
        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');

        $this->assertEquals('valid', $callbackInterpreter->interpret($utterance)[0]->getId());
    }

    /**
     * @throws FieldNotSupported
     */
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

    /**
     * @throws FieldNotSupported
     */
    public function testGetButtonValueWithAttributeName()
    {
        $customAttribute = ['age' => IntAttribute::class];
        $this->setCustomAttributes($customAttribute);

        $callbackInterpreter = $this->getCallbackInterpreter();

        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');
        $utterance->setValue('age.21');

        $intent = $callbackInterpreter->interpret($utterance)[0];

        $this->assertInstanceOf(IntAttribute::class, $intent->getNonCoreAttributes()->first()->value);
        $this->assertEquals('age', $intent->getNonCoreAttributes()->first()->value->getId());
        $this->assertEquals(21, $intent->getNonCoreAttributes()->first()->value->getValue());
    }

    /**
     * @throws FieldNotSupported
     */
    public function testCallbackInterpreterAttributeNotSet()
    {
        $utterance = new WebchatButtonResponseUtterance();
        $utterance->setCallbackId('valid');
        $utterance->setValue('not_set.21');

        $intent = $this->getCallbackInterpreter()->interpret($utterance)[0];

        $this->assertCount(1, $intent->getNonCoreAttributes());
        $this->assertEquals(StringAttribute::class, get_class($intent->getNonCoreAttributes()->first()->value));
    }

    protected function getCallbackInterpreter(): InterpreterInterface
    {
        return resolve(InterpreterServiceInterface::class)->getDefaultInterpreter();
    }
}
