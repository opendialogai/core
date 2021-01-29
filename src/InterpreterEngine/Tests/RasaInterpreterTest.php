<?php

namespace OpenDialogAi\Core\InterpreterEngine\Tests;

use Exception;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Interpreters\RasaInterpreter;
use OpenDialogAi\InterpreterEngine\Rasa\RasaClient;
use OpenDialogAi\InterpreterEngine\Rasa\RasaResponse;

class RasaInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Register some known entity types
        $entities = [
            'GPE' => 'direction_location'
        ];

        $this->setConfigValue('opendialog.interpreter_engine.rasa_entities', $entities);

        // Sets the known attributes for these tests
        $knownAttributes = [
            'direction_location' => StringAttribute::class
        ];
        $this->setConfigValue('opendialog.attribute_engine.custom_attributes', $knownAttributes);
    }

    public function testSetUp()
    {
        try {
            $interpreter = new RasaInterpreter();
            $this->assertNotNull($interpreter);
        } catch (Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromRasa()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
                new RasaResponse(json_decode(""))
            );
        });

        $interpreter = new RasaInterpreter();

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // If an exception is thrown by RASA, return a no match
    public function testErrorFromRasa()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('query')->andThrow(
                AbstractNLURequestFailedException::class
            );
        });

        $interpreter = new RasaInterpreter();

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // Use an utterance that does not support text
    public function testInvalidUtterance()
    {
        $interpreter = new RasaInterpreter();
        $this->expectException(FieldNotSupported::class);

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret(new WebchatChatOpenUtterance());

        $this->assertCount(1, $intents);
    }

    public function testMatch()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
                new RasaResponse($this->createRasaResponseObject('MATCH', 0.5))
            );
        });

        $interpreter = new RasaInterpreter();

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));
        $this->assertCount(1, $intents);

        $this->assertEquals('MATCH', $intents[0]->getLabel());
        $this->assertEquals(0.5, $intents[0]->getConfidence());
    }

    public function testCanExtractAttributes()
    {
        $this->mock(RasaClient::class, function ($mock) {
            $mock->shouldReceive('query')->andReturn(
                new RasaResponse($this->createRasaResponseObject('directions', 1, [
                    'value' => 'Accra',
                    'entity' => 'GPE',
                    'start' => 14,
                    'end' => 19
                ]))
            );
        });

        $interpreter = new RasaInterpreter();

        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('directions to accra'));
        $this->assertCount(1, $intents);

        $this->assertEquals('directions', $intents[0]->getLabel());
        $this->assertEquals(1, $intents[0]->getConfidence());

        $extractedAttributes = $intents[0]->getNonCoreAttributes();
        $this->assertCount(1, $extractedAttributes);

        /** @var AttributeInterface $attribute */
        $attribute = $extractedAttributes->first()->value;
        $this->assertEquals('direction_location', $attribute->getId());
        $this->assertEquals('Accra', $attribute->getValue());
    }

    private function createUtteranceWithText($text)
    {
        $utterance = new WebchatTextUtterance();
        $utterance->setText($text);

        return $utterance;
    }

    private function createRasaResponseObject($intentName, $confidence, $entityOptions = null)
    {
        $response = [
            'intent' => [
                'name' => $intentName,
                'confidence' => $confidence
            ]
        ];

        $hasRequiredOptions = !is_null($entityOptions)
            && !count(array_diff(['entity', 'value', 'start', 'end'], array_keys($entityOptions)));

        if ($hasRequiredOptions) {
            $response['entities'][] = [
                'entity' => $entityOptions['entity'],
                'value' => $entityOptions['value'],
                'start' => $entityOptions['start'],
                'end' => $entityOptions['end'],
                'extractor' => 'SpacyEntityExtractor',
            ];
        }

        return (json_decode(json_encode($response)));
    }
}
