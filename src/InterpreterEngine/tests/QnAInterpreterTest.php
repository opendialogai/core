<?php

namespace InterpreterEngine\tests;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;
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
        $intents = $interpreter->interpret(new WebchatChatOpenUtterance());

        $this->assertCount(1, $intents);
        $this->assertEquals(NoMatchIntent::NO_MATCH, $intents[0]->getLabel());
    }

    public function testMatch()
    {
        $this->setConfigValue('opendialog.context_engine.custom_attributes',
            ['answer' => StringAttribute::class]);

        $this->mock(QnAClient::class, function ($mock) {
            $mock->shouldReceive('queryQnA')->andReturn(
                new QnAResponse($this->createQnAResponse())
            );
        });

        $interpreter = new QnAInterpreter();
        $intents = $interpreter->interpret($this->createUtteranceWithText('no match'));
        $this->assertCount(1, $intents);

        $this->assertEquals(QnAQuestionMatchedIntent::QNA_QUESTION_MATCHED, $intents[0]->getLabel());
        $this->assertEquals(0.5, $intents[0]->getConfidence());
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
                    'context' => [
                        'isContextOnly' => false,
                        'prompts' => [],
                    ],
                ],
            ],
        ];

        return ((object) $response);
    }
}
