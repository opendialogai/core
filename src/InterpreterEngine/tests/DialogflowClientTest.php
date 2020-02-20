<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\DialogFlow\DialogFlowClient;

class DialogflowClientTest extends TestCase
{
    public function testItDoesDflow()
    {
        $dfClient = new DialogFlowClient();
        $intentMacthed = $dfClient->detectIntent('Test the framework', '1234');
        $this->assertEquals($intentMacthed, 'You are trying it already');
    }
}
