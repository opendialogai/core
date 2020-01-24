<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\DialogFlow\DialogFlowClient;

class duncanDflowTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testItDoesDflow()
    {
        $dfClient = new DialogFlowClient();
        $intentMacthed = $dfClient->detectIntent('opendialogtester-vbwobs', 'Test the framework', '1234');
        $this->assertEquals($intentMacthed, 'You are trying it already');
    }
}
