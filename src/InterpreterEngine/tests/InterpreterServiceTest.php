<?php

namespace InterpreterEngine\tests;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Intents\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class InterpreterServiceTest extends TestCase
{
    private $intent;

    public function setUp(): void
    {
        parent::setUp();
        $this->intent = (new Intent('dummy', 1))->addAttribute(new StringAttribute('name', 'test'));

        $this->mock(InterpreterServiceInterface::class, function ($mock) {
            /** @noinspection PhpUndefinedMethodInspection */
            $mock->shouldReceive('interpret')->andReturn([$this->intent]);
        });
    }

    public function testServiceBinding()
    {
        /** @var InterpreterServiceInterface $interpreterService */
        $interpreterService = $this->app->make(InterpreterServiceInterface::class);

        $intents = $interpreterService->interpret(new WebchatTextUtterance());

        $this->assertCount(1, $intents);
        $this->assertContains($this->intent, $intents);
    }
}
