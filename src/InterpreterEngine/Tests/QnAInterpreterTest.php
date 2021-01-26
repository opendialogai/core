<?php

namespace InterpreterEngine\tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Interpreters\QnAInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\QnAQuestionMatchedIntent;
use OpenDialogAi\InterpreterEngine\QnA\QnAClient;
use OpenDialogAi\InterpreterEngine\QnA\QnARequestFailedException;
use OpenDialogAi\InterpreterEngine\QnA\QnAResponse;

class QnAInterpreterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSetUp()
    {
        try {
            $interpreter = new QnAInterpreter();
            $this->assertNotNull($interpreter);
        } catch (\Exception $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testNoMatchFromQnA()
    {
        $this->mock(QnAClient::class, function ($mock) {
            $mock->shouldReceive('queryQnA')->andReturn(
                new QnAResponse(json_decode(""))
            );
        });

        $interpreter = new QnAInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // If an exception is thrown by QnA, return a no match
    public function testErrorFromQnA()
    {
        $this->mock(QnAClient::class, function ($mock) {
            $mock->shouldReceive('queryQnA')->andThrow(
                QnARequestFailedException::class
            );
        });

        $interpreter = new QnAInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    // Use an utterance that does not support text
    public function testInvalidUtterance()
    {
        $interpreter = new QnAInterpreter();
        $this->expectException(FieldNotSupported::class);

        $intents = $interpreter->interpret(new WebchatChatOpenUtterance());

        $this->assertCount(1, $intents);
    }

    public function testMatch()
    {
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['answer' => StringAttribute::class]
        );

        $this->mock(QnAClient::class, function ($mock) {
            $mock->shouldReceive('queryQnA')->andReturn(
                new QnAResponse($this->createQnAResponse())
            );
        });

        $interpreter = new QnAInterpreter();
        /** @var Intent[] $intents */
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));
        $this->assertCount(1, $intents);

        $this->assertEquals(QnAQuestionMatchedIntent::QNA_QUESTION_MATCHED, $intents[0]->getLabel());
        $this->assertEquals(0.5, $intents[0]->getConfidence());

        $answer = $intents[0]->getNonCoreAttributes()->get('qna_answer');
        $this->assertEquals('People created me.', $answer->getValue());

        $prompt0 = $intents[0]->getNonCoreAttributes()->get('qna_prompt_0');
        $prompt1 = $intents[0]->getNonCoreAttributes()->get('qna_prompt_1');
        $this->assertEquals('What is an audit?', $prompt0->getValue());
        $this->assertEquals('Where are you?', $prompt1->getValue());
    }

    private function createUtteranceWithText($text)
    {
        $utterance = new WebchatTextUtterance();
        $utterance->setText($text);

        return $utterance;
    }

    private function createQnAResponseNoMatch()
    {
        $response = [
            'answers' => [
                (object) [
                    'questions' => [],
                    'answer' => 'No good match found in KB.',
                    'score' => 0,
                    'id' => -1,
                    'source' => null,
                    'metadata' => [],
                ],
            ],
        ];

        return ((object) $response);
    }

    private function createQnAResponse()
    {
        $response = [
            'answers' => [
                (object) [
                    'questions' => [
                        'Who created you?',
                        'Where did you come from?',
                        'Who made you?',
                        'Who is your creator?',
                        'Which people made you?',
                        'Who owns you?',
                    ],
                    'answer' => 'People created me.',
                    'score' => 50,
                    'id' => 8,
                    'source' => 'qna_chitchat_the_professional.tsv',
                    'metadata' => [
                        'name' => 'editorial',
                        'value' => 'chitchat',
                    ],
                    'context' => (object) [
                        'isContextOnly' => false,
                        'prompts' => [
                            (object) [
                                'displayOrder' => 0,
                                'qnaId' => 101,
                                'qna' => null,
                                'displayText' => 'What is an audit?',
                            ],
                            (object) [
                                'displayOrder' => 0,
                                'qnaId' => 32,
                                'qna' => null,
                                'displayText' => 'Where are you?',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return ((object) $response);
    }
}
