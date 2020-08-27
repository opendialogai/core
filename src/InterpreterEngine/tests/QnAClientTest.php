<?php

namespace InterpreterEngine\tests;

use GuzzleHttp\Client;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\QnA\QnAClient;
use OpenDialogAi\InterpreterEngine\QnA\QnAResponse;

class QnAClientTest extends TestCase
{
    public function testQnAConfig()
    {
        $config = [
            'app_url' => 'app_url',
            'endpoint_key' => 'endpoint_key',
        ];

        $this->setConfigValue('opendialog.interpreter_engine.qna_config', $config);

        $client = $this->app->make(QnAClient::class);

        $this->assertEquals(QnAClient::class, get_class($client));
    }

    public function testQnAResponse()
    {
        $qnaResponse = <<<EOT
{
  "answers": [
    {
      "questions": [
        "Who created you?",
        "Where did you come from?",
        "Who made you?",
        "Who is your creator?",
        "Which people made you?",
        "Who owns you?"
      ],
      "answer": "People created me.",
      "score": 100,
      "id": 8,
      "source": "qna_chitchat_the_professional.tsv",
      "metadata": [
        {
          "name": "editorial",
          "value": "chitchat"
        }
      ],
      "context": {
        "isContextOnly": false,
        "prompts": [
          {
            "displayOrder": 0,
            "qnaId": 101,
            "qna": null,
            "displayText": "What is an audit?"
          },
          {
            "displayOrder": 0,
            "qnaId": 32,
            "qna": null,
            "displayText": "Where are you?"
          }
        ]
      }
    }
  ],
  "debugInfo": null
}
EOT;
        $response = new QnAResponse(json_decode($qnaResponse));
        $answers = $response->getAnswers();

        $this->assertCount(1, $answers);

        $this->assertCount(6, $answers[0]->getQuestions());
        $this->assertEquals('People created me.', $answers[0]->getAnswer());
        $this->assertEquals(100, $answers[0]->getScore());
        $this->assertEquals(8, $answers[0]->getId());
        $this->assertEquals('qna_chitchat_the_professional.tsv', $answers[0]->getSource());
        $this->assertCount(1, $answers[0]->getMetadata());

        $prompts = $answers[0]->getPrompts();
        $this->assertCount(2, $prompts);

        $this->assertEquals('What is an audit?', $prompts[0]->getDisplayText());
        $this->assertEquals(0, $prompts[0]->getDisplayOrder());
        $this->assertEquals(101, $prompts[0]->getQnaId());
        $this->assertEquals('Where are you?', $prompts[1]->getDisplayText());
        $this->assertEquals(0, $prompts[1]->getDisplayOrder());
        $this->assertEquals(32, $prompts[1]->getQnaId());
    }
}
