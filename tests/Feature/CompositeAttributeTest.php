<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\Bot\Actions\TestAction;
use OpenDialogAi\Core\Tests\Bot\Interpreters\TestInterpreterComposite;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class CompositeAttributeTest extends TestCase
{
    public function testCompositeAttribute()
    {
        // createFromInput
        $attributeCollection = new ExampleAbstractAttributeCollection(
            array(['id' => 'one', 'value' => 'go']),
            'array'
        );

        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
            [
                'c' => ExampleAbstractCompositeAttribute::class,
                'test_attr' => StringAttribute::class,
                'total' => IntAttribute::class,
                'results' => ArrayAttribute::class
            ]
        );
        $attributeCollectionSerialized = $attributeCollection->jsonSerialize();
        $compositeAttributeFromSerializedCollection = AttributeResolver::getAttributeFor('c', $attributeCollectionSerialized);

        $compositeAttribute = (AttributeResolver::getAttributeFor('c', $attributeCollection));

        $this->assertEquals($compositeAttributeFromSerializedCollection, $compositeAttribute);
        $this->assertEquals($attributeCollection->getAttributes(), $compositeAttribute->getValue());
        $this->assertEquals($compositeAttribute->getType(), AbstractCompositeAttribute::$type);
        $this->assertEquals(get_class($compositeAttribute->getValue()[0]), IntAttribute::class);
        $this->assertEquals(get_class($compositeAttribute->getValue()[1]), ArrayAttribute::class);

        //JSON deserialize
        $attributeCollectionNew = new ExampleAbstractAttributeCollection(
            json_encode(array(['id' => 'test_attr', 'value' => 'go']))
        );
        $compositeAttributeNew = new ExampleAbstractCompositeAttribute(
            'n',
            $attributeCollectionNew
        );

        $this->assertEquals($attributeCollectionNew->jsonSerialize(), '[{&quot;id&quot;:&quot;test_attr&quot;,&quot;value&quot;:&quot;go&quot;}]');
    }

    /**
     * @requires DGRAPH
     */
    public function testCompositeAttributesWithUserContext()
    {
        $this->registerSingleInterpreter(new TestInterpreterComposite());

        $this->registerSingleAction(new TestAction());

        $this->setCustomAttributes(
            [
                'total' => IntAttribute::class,
                'results' => ArrayAttribute::class,
                'array_test' => ArrayAttribute::class,
                'result_test' => ExampleAbstractCompositeAttribute::class,
            ]
        );

        $this->activateConversation($this->getConversation());

        /** @var OutgoingIntent $intent */
        $intent = OutgoingIntent::create(['name' => 'intent.test.hello_user']);

        $markUp = (new MessageMarkUpGenerator())->addTextMessage('Result: {user.array_test} || {user.result_test}');

        $messageTemplate = MessageTemplate::create(
            [
                'name' => 'Test message',
                'message_markup' => $markUp->getMarkUp(),
                'outgoing_intent_id' => $intent->id
            ]
        );

        $intent->messageTemplates()->save($messageTemplate);

        $utterance = UtteranceGenerator::generateTextUtterance('Hello');
        $messages = resolve(OpenDialogController::class)->runConversation($utterance);
        $this->assertCount(1, $messages->getMessages());

        $compositeAttributeCollection = new ExampleAbstractCompositeAttribute(
            'result_test',
            new ExampleAbstractAttributeCollection(
                ['id' => 'one', 'value' => 'go'],
                'array'
            )
        );
        $arrayAttribute = new ArrayAttribute('array_test', ['ok']);
        $this->assertEquals(
            'Result: '
            . $arrayAttribute->toString() . ' || '
            . $compositeAttributeCollection->toString(),
            $messages->getMessages()[0]->getText()
        );
    }


    private function getConversation()
    {
        return <<<EOT
conversation:
  id: hello_bot
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.test.hello_bot_comp
            interpreter: interpreter.test.hello_bot_comp
            action: action.test.test
            expected_attributes:
              - id: user.array_test
              - id: user.result_test
        - b:
            i: intent.test.hello_user
            completes: true
EOT;
    }
}
