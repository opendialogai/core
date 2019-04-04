<?php

namespace InterpreterEngine\tests;

use InterpreterEngine\Interpreters\LuisInterpreter;
use InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\Luis\LuisRequestFailedException;
use OpenDialogAi\InterpreterEngine\Luis\LuisResponse;

// TODO still needs tests for: pulling attributes out of luis, bound and non bound attributes
class LuisInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Register some known entity types
        $entities = [
            'intEntity' => IntAttribute::class,
            'floatEntity' => FloatAttribute::class
        ];

        $this->setConfigValue('opendialog.interpreter_engine.luis_entities', $entities);
    }

    public function testSetUp()
    {
        try {
            $interpreter = new LuisInterpreter();
            $this->assertNotNull($interpreter);
        } catch (\Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromLuis()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('queryLuis')->andReturn(
                new LuisResponse(json_decode(""))
            );
        });

        $interpreter = new LuisInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // If an exception is thrown by LUIS, return a no match
    public function testErrorFromLuis()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('queryLuis')->andThrow(
                LuisRequestFailedException::class
            );
        });

        $interpreter = new LuisInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // Use an utterance that does not support text
    public function testInvalidUtterance()
    {
        $interpreter = new LuisInterpreter();
        $intents = $interpreter->interpret(new WebchatChatOpenUtterance());

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    public function testMatch()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('queryLuis')->andReturn(
                new LuisResponse($this->createLuisResponseObject('MATCH', 0.5))
            );
        });

        $interpreter = new LuisInterpreter();
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

    private function createLuisResponseObject($intentName, $confidence)
    {
        $response = [
            'topScoringIntent' => [
                'intent' => $intentName,
                'score' => $confidence
            ]
        ];

        return json_decode(json_encode($response));
    }
}
