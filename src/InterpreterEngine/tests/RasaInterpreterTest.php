<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Interpreters\RasaInterpreter;
use OpenDialogAi\InterpreterEngine\Rasa\RasaClient;
use OpenDialogAi\InterpreterEngine\Rasa\RasaRequestFailedException;
use OpenDialogAi\InterpreterEngine\Rasa\RasaResponse;

class RasaInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Register some known entity types
        $entities = [
            'name' => 'first_name',
            'age_in_years' => 'age'
        ];

        $this->setConfigValue('opendialog.interpreter_engine.rasa_entities', $entities);

        // Sets the known attributes for these tests
        $knownAttributes = [
            'first_name' => StringAttribute::class,
            'age' => IntAttribute::class
        ];
        $this->setConfigValue('opendialog.context_engine.custom_attributes', $knownAttributes);
    }

    public function testSetUp()
    {
        try {
            $interpreter = new RasaInterpreter();
            $this->assertNotNull($interpreter);
        } catch (\Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromRasa()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('queryRasa')->andReturn(
                new RasaResponse(json_decode(""))
            );
        });

        $interpreter = new RasaInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // If an exception is thrown by RASA, return a no match
    public function testErrorFromRasa()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('queryRasa')->andThrow(
                RasaRequestFailedException::class
            );
        });

        $interpreter = new RasaInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // Use an utterance that does not support text
    public function testInvalidUtterance()
    {
        $interpreter = new RasaInterpreter();
        $intents = $interpreter->interpret(new WebchatChatOpenUtterance());

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    public function testMatch()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('queryRasa')->andReturn(
                new RasaResponse($this->createRasaResponseObject('MATCH', 0.5))
            );
        });

        $interpreter = new RasaInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));
        $this->assertCount(1, $intents);

        $this->assertEquals('MATCH', $intents[0]->getLabel());
        $this->assertEquals(0.5, $intents[0]->getConfidence());
    }

    private function createUtteranceWithText($text)
    {
        $utterance = new WebchatTextUtterance();
        $utterance->setText($text);

        return $utterance;
    }

    private function createRasaResponseObject($intentName, $confidence, $entityType = null, $entityValue = null)
    {
        $response = [
            'intent' => [
                'name' => $intentName,
                'confidence' => $confidence
            ]
        ];

        if ($entityType && $entityValue) {
            $response['entities'][] = [
                'entity' => $entityValue,
                'value' => $entityType,
                'start' => 0,
                'end' => 1,
                'extractor' => 'SpacyEntityExtractor',
            ];
        }

        return (json_decode(json_encode($response)));
    }
}
