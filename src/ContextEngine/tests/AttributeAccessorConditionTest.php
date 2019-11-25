<?php

namespace OpenDialogAi\ContextEngine\tests;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\OperationEngine\Operations\EquivalenceOperation;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\Bot\Interpreters\TestInterpreterComposite;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

/**
 * Class AttributeAccessorTest
 *
 * @package OpenDialogAi\ContextEngine\tests
 */
class AttributeAccessorConditionTest extends TestCase
{

    /** @var OperationServiceInterface */
    private $operationService;

    public function testConditionGreaterThanComparisonWithCompositeAttribute()
    {
        $this->operationService = app()->make(OperationServiceInterface::class);

        $testData = [1 => 'one', 2 => 'two', 3 => 'three'];
        $parameters = ['value' => count($testData)];
        $attributes = ['user.name' => 'testing'];
        $condition = new Condition(EquivalenceOperation::$name, $attributes, $parameters);

        $compositeAttribute = new ExampleAbstractCompositeAttribute(
            'CompositeAttribute',
            new ExampleAbstractAttributeCollection(
                $testData,
                ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY
            )
        );

        $this->addAttributeToSession($compositeAttribute);
        $attributeToCompare = ContextService::getAttributeValue('CompositeAttribute', 'session', ['total']);


        $operation = $this->operationService->getOperation(
            $condition->getEvaluationOperation()
        );
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes(['test' => $attributeToCompare]);

        $this->assertTrue($operation->execute());
    }

    public function testCompositeAttributesWithUserContext()
    {
        $this->registerSingleInterpreter(new TestInterpreterComposite());
        $this->setCustomAttributes(
            [
                'total' => IntAttribute::class,
                'results' => ArrayAttribute::class,
                'array_test' => ArrayAttribute::class,
                'result_test' => ExampleAbstractCompositeAttribute::class,
            ]
        );
        $compositeAttributeCollection = new ExampleAbstractCompositeAttribute(
            'result_test',
            new ExampleAbstractAttributeCollection(
                ['id' => 'one', 'value' => 'go'],
                ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY
            )
        );
        $arrayAttribute = new ArrayAttribute('array_test', ['ok']);


        $this->activateConversation($this->getConversation());
        /** @var OutgoingIntent $intent */
        $intent = OutgoingIntent::create(['name' => 'intent.test.hello_user']);


        $markUp = (new MessageMarkUpGenerator())->addTextMessage(
            'Result: {user.array_test} || {user.result_test} || {user.result_test.total} || {user.result_test.results}'
        );
        $messageTemplate = MessageTemplate::create(
            [
                'name' => 'Test message',
                'message_markup' => $markUp->getMarkUp(),
                'outgoing_intent_id' => $intent->id,
            ]
        );


        $intent->messageTemplates()->save($messageTemplate);
        $utterance = UtteranceGenerator::generateTextUtterance('Hello');
        $messages = resolve(OpenDialogController::class)->runConversation($utterance);


        $this->assertCount(1, $messages->getMessages());
        $this->assertEquals(
            'Result: '
            . $arrayAttribute->toString() . ' || '
            . $compositeAttributeCollection->toString() . ' || '
            . $compositeAttributeCollection->getValue(['total'])
                ->toString() . ' || '
            . $compositeAttributeCollection->getValue(['results'])->toString(),
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
            expected_attributes:
              - id: user.array_test
              - id: user.result_test
        - b:
            i: intent.test.hello_user
            conditions:
              - condition:
                  operation: gt
                  attributes:
                    attr: user.result_test.total
                  parameters:
                    value: 1
            completes: true
EOT;
    }

    private function addAttributeToSession($attribute): void
    {
        ContextService::getSessionContext()->addAttribute($attribute);
    }
}
