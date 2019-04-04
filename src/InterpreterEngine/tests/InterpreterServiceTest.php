<?php

namespace InterpreterEngine\tests;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNotRegisteredException;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\tests\Interpreters\DummyInterpreter;
use OpenDialogAi\InterpreterEngine\tests\Interpreters\NoNameInterpreter;

class InterpreterServiceTest extends TestCase
{
    public function testServiceBinding()
    {
        $intent = (new Intent('dummy', 1))->addAttribute(new StringAttribute('name', 'test'));

        // Mock the service
        $this->mock(InterpreterServiceInterface::class, function ($mock) use ($intent) {
            /** @noinspection PhpUndefinedMethodInspection */
            $mock->shouldReceive('interpret')->andReturn([$intent]);
        });

        $interpreterService = $this->getBoundInterpreterService();

        $intents = $interpreterService->interpret('test', new WebchatTextUtterance());

        $this->assertCount(1, $intents);
        $this->assertContains($intent, $intents);
    }

    public function testAvailableInterpreters()
    {
        $interpreterName = 'interpreter.test.dummy';
        $mockInterpreter = $this->createMockInterpreter($interpreterName);
        $this->registerInterpreter($mockInterpreter);

        $interpreterService = $this->getBoundInterpreterService();

        $interpreters = $interpreterService->getAvailableInterpreters();

        $this->assertCount(1, $interpreters);
        $this->assertContains($interpreterName, array_keys($interpreters));
    }

    public function testInterpreterWithBadName()
    {
        $mockInterpreter = $this->createMockInterpreter('bad name');
        $this->registerInterpreter($mockInterpreter);

        // Should not have been bound
        $this->assertCount(0, $this->getBoundInterpreterService()->getAvailableInterpreters());
    }

    /**
     * @throws InterpreterNameNotSetException
     */
    public function testGetInterpreter()
    {
        $interpreterName = 'interpreter.test.dummy';
        $mockInterpreter = $this->createMockInterpreter($interpreterName);
        $this->registerInterpreter($mockInterpreter);

        $interpreterService = $this->getBoundInterpreterService();

        $this->assertEquals($interpreterName, $interpreterService->getInterpreter($interpreterName)::getName());
    }

    /**
     * @throws InterpreterNameNotSetException
     */
    public function testRealInterpreter()
    {
        $this->registerInterpreter(new DummyInterpreter());
        $service = $this->getBoundInterpreterService();
        $intents = $service->interpret(DummyInterpreter::getName(), new WebchatTextUtterance());

        $this->assertCount(1, $intents);
        $this->assertEquals('dummy', $intents[0]->getLabel());
    }

    /**
     * @throws InterpreterNameNotSetException
     */
    public function testInterpretNonBoundInterpreter()
    {
        // No interpreters have been bound, so expect an exception
        $service = $this->getBoundInterpreterService();

        try {
            $service->interpret(DummyInterpreter::getName(), new WebchatTextUtterance());
            $this->fail('Exception should have been thrown');
        } catch (InterpreterNotRegisteredException $e) {
            $this->assertNotNull($e);
        }
    }

    public function testInterpreterNoNameNotRegistered()
    {
        $this->registerInterpreter(new NoNameInterpreter());
        $service = $this->getBoundInterpreterService();
        $interpreters = $service->getAvailableInterpreters();

        $this->assertCount(0, $interpreters);
    }

    private function registerInterpreter($mockInterpreter): void
    {
        $this->app['config']->set('opendialog.interpreter_engine.available_interpreters', [get_class($mockInterpreter)]);
    }

    /**
     * @param $interpreterName
     * @return \Mockery\MockInterface|InterpreterInterface
     */
    protected function createMockInterpreter($interpreterName)
    {
        $mockInterpreter = \Mockery::mock(InterpreterInterface::class);
        $mockInterpreter->shouldReceive('getName')->andReturn($interpreterName);

        return $mockInterpreter;
    }

    /**
     * @return InterpreterServiceInterface
     */
    private function getBoundInterpreterService(): InterpreterServiceInterface
    {
        $interpreterService = $this->app->make(InterpreterServiceInterface::class);
        return $interpreterService;
    }
}
