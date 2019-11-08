<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Interpreters\LuisInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\Luis\LuisRequestFailedException;
use OpenDialogAi\InterpreterEngine\Luis\LuisResponse;

class LuisInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Register some known entity types
        $entities = [
            'name' => 'first_name',
            'age_in_years' => 'age'
        ];

        $this->setConfigValue('opendialog.interpreter_engine.luis_entities', $entities);

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
            $interpreter = new LuisInterpreter();
            $this->assertNotNull($interpreter);
        } catch (\Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromLuis()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
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
            $mock->shouldReceive('query')->andThrow(
                AbstractNLURequestFailedException::class
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
            $mock->shouldReceive('query')->andReturn(
                new LuisResponse($this->createLuisResponseObject('MATCH', 0.5))
            );
        });

        $interpreter = new LuisInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));
        $this->assertCount(1, $intents);

        $this->assertEquals('MATCH', $intents[0]->getLabel());
        $this->assertEquals(0.5, $intents[0]->getConfidence());
    }

    public function testMatchWithKnownEntity()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
                new LuisResponse(
                    $this->createLuisResponseObject(
                        'MATCH',
                        0.5,
                        'age_in_years',
                        'entity'
                    )
                )
            );
        });

        $interpreter = new LuisInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('match'));
        $this->assertCount(1, $intents);

        $matchedAttribute = $intents[0]->getAttribute('age');
        $this->assertEquals(IntAttribute::class, get_class($matchedAttribute));
    }

    public function testMatchWithUnknownEntity()
    {
        $this->mock(LuisClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
                new LuisResponse(
                    $this->createLuisResponseObject(
                        'MATCH',
                        0.5,
                        'unknownEntity',
                        'entity'
                    )
                )
            );
        });

        $interpreter = new LuisInterpreter();

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('match'));
        $this->assertCount(1, $intents);

        $this->assertCount(1, $intents[0]->getNonCoreAttributes());

        $matchedAttribute = $intents[0]->getAttribute('unknownEntity');
        $this->assertEquals(StringAttribute::class, get_class($matchedAttribute));
    }

    private function createUtteranceWithText($text)
    {
        $utterance = new WebchatTextUtterance();
        $utterance->setText($text);

        return $utterance;
    }

    private function createLuisResponseObject($intentName, $confidence, $entityType = null, $entityValue = null)
    {
        $response = [
            'topScoringIntent' => [
                'intent' => $intentName,
                'score' => $confidence
            ]
        ];

        if ($entityType && $entityValue) {
            $response['entities'][] = [
                'entity' => $entityValue,
                'type' => $entityType,
                'startIndex' => 0,
                'endIndex' => 1,
                'resolution' => [
                    'values' => [$entityValue]
                ]
            ];
        }

        return (json_decode(json_encode($response)));
    }
}
