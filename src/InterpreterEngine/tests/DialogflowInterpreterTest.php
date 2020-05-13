<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;

use Google\Cloud\Dialogflow\V2\Intent;
use Google\Cloud\Dialogflow\V2\Intent\Message;
use Google\Cloud\Dialogflow\V2\Intent\Message\Platform;
use Google\Cloud\Dialogflow\V2\Intent\Message\SimpleResponse;
use Google\Cloud\Dialogflow\V2\Intent\Message\SimpleResponses;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowClient;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowMessageTransformer;
use OpenDialogAi\InterpreterEngine\Interpreters\DialogflowInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;

class DialogflowInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        putenv('DIALOG_FLOW_DEFAULT_PROJECT_ID=test');
    }

    public function testSetUp()
    {
        try {
            $interpreter = new DialogflowInterpreter();
            $this->assertNotNull($interpreter);
        } catch (\Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromDialogflow()
    {
        $interpreter = new DialogflowInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    public function testDialogflowMessageTransformer()
    {
        $intent = new Intent([
            'name' => 'projects/test',
            'display_name' => 'Simple response',
        ]);

        $simpleResponses = new SimpleResponses([
            'simple_responses' => [
                new SimpleResponse([
                    'text_to_speech' => 'This is just a simple text response',
                ]),
            ],
        ]);
        $fulfillmentMessages = new RepeatedField(GPBType::MESSAGE, Message::class);
        $fulfillmentMessages[] = new Message([
            'platform' => Platform::ACTIONS_ON_GOOGLE,
            'simple_responses' => $simpleResponses,
        ]);

        $data = [
            'query_text' => 'simple',
            'all_required_params_present' => true,
            'intent_detection_confidence' => 0.5,
            'language_code' => 'en',
            'fulfillment_messages' => $fulfillmentMessages,
            'intent' => $intent,
        ];

        $requestContents = new QueryResult($data);

        $client = new DialogflowClient();
        $response = $client->createResponse($requestContents);

        $dialogflowMessage = '';
        foreach ($response->getResponseMessages() as $responseMessage) {
            if ($responseMessage->getPlatform() == Platform::ACTIONS_ON_GOOGLE) {
                $dialogflowMessage = DialogflowMessageTransformer::interpretMessages($responseMessage);
            }
        }

        $this->assertEquals('<text-message>This is just a simple text response</text-message>', $dialogflowMessage);
    }

    private function createUtteranceWithText($text)
    {
        $utterance = new WebchatTextUtterance();
        $utterance->setText($text);

        return $utterance;
    }
}
