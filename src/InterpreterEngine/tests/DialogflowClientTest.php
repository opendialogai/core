<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use Google\Cloud\Dialogflow\V2\Intent;
use Google\Cloud\Dialogflow\V2\Intent\Message;
use Google\Cloud\Dialogflow\V2\Intent\Message\Platform;
use Google\Cloud\Dialogflow\V2\Intent\Message\SimpleResponse;
use Google\Cloud\Dialogflow\V2\Intent\Message\SimpleResponses;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowClient;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowEntity;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowIntent;
use OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowRequest;

class DialogflowClientTest extends TestCase
{
    public function testDialogflowRequest()
    {
        $queryResult = new QueryResult([]);

        $request = new DialogflowRequest($queryResult);

        $this->assertEquals('Google\Cloud\Dialogflow\V2\QueryResult', get_class($request->getContents()));
        $this->assertEquals(true, $request->isSuccessful());
    }

    public function testDialogflowResponse()
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

        $this->assertEquals('OpenDialogAi\InterpreterEngine\Dialogflow\DialogflowResponse', get_class($response));
        $this->assertEquals('Google\Protobuf\Internal\RepeatedField', get_class($response->getResponseMessages()));
        $this->assertEquals(1, count($response->getResponseMessages()));
        $this->assertEquals('', $response->getResponse());
        $this->assertEquals(false, $response->isCompleting());
    }

    public function testDialogflowEntity()
    {
        $dialogflowEntity = [
            'name' => 'Test name',
            'value' => 'Test value',
        ];

        $entity = new DialogflowEntity($dialogflowEntity);

        $this->assertEquals('Test name', $entity->getType());
        $this->assertEquals(1, count($entity->getResolutionValues()));
        $this->assertEquals('Test value', $entity->getResolutionValues()[0]);
    }

    public function testDialogflowIntent()
    {
        $intent = new DialogflowIntent('Test', 0.96);

        $this->assertEquals('Test', $intent->getLabel());
        $this->assertEquals(0.96, $intent->getConfidence());
    }
}
