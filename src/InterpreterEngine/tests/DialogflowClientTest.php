<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use GuzzleHttp\Client;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\DialogFlow\DialogflowClient;

class DialogflowClientTest extends TestCase
{
    private $guzzleMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->guzzleMock = \Mockery::mock(Client::class);
    }

    public function testItsInstantiable()
    {
        $config = [
            'app_url' => 'https://dialogflow.googleapis.com/v2/',
        ];
        $dfClient = new DialogflowClient($this->guzzleMock, $config);
        $this->assertInstanceOf(DialogflowClient::class, $dfClient);
    }

    public function testItCanMakeARequest()
    {
        $config = [
            'app_url' => 'https://dialogflow.googleapis.com/v2/',
        ];
        $dfClient = new DialogflowClient($this->guzzleMock, $config);

        $this->guzzleMock->shouldReceive('GET')->once();

        $dfClient->sendRequest('This is a test');
    }
}
