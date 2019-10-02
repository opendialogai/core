<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Exceptions\DefaultInterpreterNotDefined;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNameNotSetException;
use OpenDialogAi\InterpreterEngine\Exceptions\InterpreterNotRegisteredException;
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
        $this->registerSingleInterpreter($mockInterpreter);

        $interpreterService = $this->getBoundInterpreterService();

        $interpreters = $interpreterService->getAvailableInterpreters();

        $this->assertCount(1, $interpreters);
        $this->assertContains($interpreterName, array_keys($interpreters));
    }

    public function testInterpreterWithBadName()
    {
        $mockInterpreter = $this->createMockInterpreter('bad name');
        $this->registerSingleInterpreter($mockInterpreter);

        // Should not have been bound
        $this->expectException(DefaultInterpreterNotDefined::class);
        $this->assertCount(0, $this->getBoundInterpreterService()->getAvailableInterpreters());
    }

    /**
     * @throws InterpreterNameNotSetException
     */
    public function testGetInterpreter()
    {
        $interpreterName = 'interpreter.test.dummy';
        $mockInterpreter = $this->createMockInterpreter($interpreterName);
        $this->registerSingleInterpreter($mockInterpreter);

        $interpreterService = $this->getBoundInterpreterService();

        $this->assertEquals($interpreterName, $interpreterService->getInterpreter($interpreterName)::getName());
    }

    /**
     * @throws InterpreterNameNotSetException
     */
    public function testRealInterpreter()
    {
        $this->registerSingleInterpreter(new DummyInterpreter());
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
        $this->expectException(InterpreterNameNotSetException::class);
        $this->registerSingleInterpreter(new NoNameInterpreter());
    }

    public function testForLuisInterpreter()
    {
        $service = $this->getBoundInterpreterService();
        $this->assertNotNull($service->getInterpreter('interpreter.core.luis'));
    }

    public function testForQnAInterpreter()
    {
        $service = $this->getBoundInterpreterService();
        $this->assertNotNull($service->getInterpreter('interpreter.core.qna'));
    }

    public function testDefaultInterpreterSetting()
    {
        $service = $this->getBoundInterpreterService();
        $service->setDefaultInterpreter('interpreter.core.callbackInterpreter');
        $defaultInterpreter = $service->getDefaultInterpreter();

        $this->assertEquals('interpreter.core.callbackInterpreter', $defaultInterpreter::getName());
    }

    public function testSupportedCallbacksForCallbackInterpreter()
    {
        $service = $this->getBoundInterpreterService();
        $service->setDefaultInterpreter('interpreter.core.callbackInterpreter');
        $defaultInterpreter = $service->getDefaultInterpreter();

        $utterance = new WebchatChatOpenUtterance();
        $utterance->setCallbackId('chat_open');

        $intents = $defaultInterpreter->interpret($utterance);
        $this->assertCount(1, $intents);
        $intent = $intents[0];
        $this->assertEquals('intent.core.chatOpen', $intent->getId());
    }

    public function testInterpreterResultCache()
    {
        $this->registerSingleInterpreter(new DummyInterpreter());
        $service = $this->getBoundInterpreterService();
        $utterance = new WebchatTextUtterance();

        $interpreterName = DummyInterpreter::getName();

        $intents = $service->interpret($interpreterName, $utterance);

        Log::shouldReceive('info')
            ->with('Getting result from the cache for interpreter ' . $interpreterName);

        $intentsFromCache = $service->interpret($interpreterName, $utterance);

        $this->assertCount(1, $intents);
        $this->assertCount(1, $intentsFromCache);
        $this->assertEquals('dummy', $intents[0]->getLabel());
        $this->assertEquals('dummy', $intentsFromCache[0]->getLabel());

        $interpreterCacheTime = $service->getInterpreterCacheTime($interpreterName);
        $this->assertEquals(60, $interpreterCacheTime);

        $this->app['config']->set('opendialog.interpreter_engine.interpreter_cache_times', [$interpreterName => 1000]);

        $interpreterCacheTime = $service->getInterpreterCacheTime($interpreterName);
        $this->assertEquals(1000, $interpreterCacheTime);
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
