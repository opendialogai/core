<?php

namespace OpenDialogAi\Core\OperationEngine\Tests;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\Bot\Interpreters\TestInterpreterComposite;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\OperationEngine\OperationInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;
use OpenDialogAi\OperationEngine\Tests\Operations\DummyOperation;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class OperationServiceTest extends TestCase
{
    public function testAvailableOperations()
    {
        $operationName = 'dummy';
        $mockOperation = $this->createMockOperation($operationName);
        $this->registerOperation($mockOperation);

        $operationService = $this->getBoundOperationService();

        $operations = $operationService->getAvailableOperations();

        $this->assertCount(1, $operations);
        $this->assertContains($operationName, array_keys($operations));
    }

    public function testGetOperation()
    {
        $operationName = 'dummy';
        $mockOperation = $this->createMockOperation($operationName);
        $this->registerOperation($mockOperation);

        $operationService = $this->getBoundOperationService();

        $this->assertEquals($operationName, $operationService->getOperation($operationName)::getName());
    }

    /**
     * @requires DGRAPH
     */
    public function testConversationConditionWithOnlyOperation()
    {
        $this->app['config']->set(
            'opendialog.operation_engine.available_operations',
            [
                DummyOperation::class
            ]
        );

        $this->registerSingleInterpreter(new TestInterpreterComposite());

        $this->activateConversation($this->getConversation());

        /** @var OutgoingIntent $intent */
        $intent = OutgoingIntent::create(['name' => 'intent.test.hello_user']);

        $markUp = (new MessageMarkUpGenerator())->addTextMessage('Example test message');
        $messageTemplate = MessageTemplate::create(
            [
                'name' => 'Test message',
                'message_markup' => $markUp->getMarkUp(),
                'outgoing_intent_id' => $intent->id,
            ]
        );

        $utterance = UtteranceGenerator::generateTextUtterance('Hello');
        $messages = resolve(OpenDialogController::class)->runConversation($utterance);

        $this->assertCount(1, $messages->getMessages());
        $this->assertEquals('Example test message', $messages->getMessages()[0]->getText());
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
        - b:
            i: intent.test.hello_user
            conditions:
              - condition:
                  operation: dummy
            completes: true
EOT;
    }

    private function registerOperation($mockOperation): void
    {
        $this->app['config']->set(
            'opendialog.operation_engine.available_operations',
            [
                get_class($mockOperation)
            ]
        );
    }

    /**
     * @param $operationName
     * @return \Mockery\MockInterface|OperationInterface
     */
    protected function createMockOperation($operationName)
    {
        $mockOperation = \Mockery::mock(OperationInterface::class);
        $mockOperation->shouldReceive('getName')->andReturn($operationName);

        return $mockOperation;
    }

    /**
     * @return OperationServiceInterface
     */
    private function getBoundOperationService(): OperationServiceInterface
    {
        $operationService = $this->app->make(OperationServiceInterface::class);
        return $operationService;
    }
}
