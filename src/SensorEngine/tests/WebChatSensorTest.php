<?php

namespace OpenDialogAi\Core\SensorEngine\tests;

use Illuminate\Http\Request;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;

class WebChatSensorTest extends WebchatSensorTestBase
{
    /**
     * @var WebchatSensor
     */
    private $sensor;

    public function setUp(): void
    {
        parent::setUp();
        $this->sensor = new WebchatSensor();
    }

    public function testFormResponse()
    {
        $data = [
            'name' => 'value',
            'callback_id' => 'callback_id'
        ];
        $body = $this->generateResponseMessage('webchat_form_response', $data);

        $utterance = $this->sensor->interpret(new Request($body));

        $this->assertCount(1, $utterance->getFormValues());
        $this->assertEquals(['name' => 'value'], $utterance->getFormValues());
        $this->assertEquals('callback_id', $utterance->getCallbackId());
    }
}
