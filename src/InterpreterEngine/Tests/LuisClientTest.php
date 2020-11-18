<?php

namespace OpenDialogAi\Core\InterpreterEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\Luis\LuisEntity;
use OpenDialogAi\InterpreterEngine\Luis\LuisIntent;
use OpenDialogAi\InterpreterEngine\Luis\LuisResponse;

/**
 * TODO Add a test mocking the guzzle client
 */
class LuisClientTest extends TestCase
{
    public function testLuisConfig()
    {
        $config = [
            'app_url' => 'app_url',
            'app_id' => 'app_id',
            'staging' => 'staging',
            'subscription_key' => 'subscription_key',
            'timezone_offset' => 'timezone_offset',
            'verbose' => 'verbose',
            'spellcheck' => 'spellcheck',
        ];

        $this->setConfigValue('opendialog.interpreter_engine.luis_config', $config);

        $client = $this->app->make(LuisClient::class);

        $this->assertEquals(LuisClient::class, get_class($client));
    }

    public function testLuisResponse()
    {
        $luisResponse = <<<EOT
{
  "query": "I am in zurich",
  "topScoringIntent": {
    "intent": "Define Canton",
    "score": 0.96
  },
  "entities": [
    {
      "entity": "zurich",
      "type": "SwissCanton",
      "startIndex": 8,
      "endIndex": 13,
      "resolution": {
        "values": [
          "zurich"
        ]
      }
    }
  ]
}
EOT;
        $response = new LuisResponse(json_decode($luisResponse));

        $this->assertEquals(LuisIntent::class, get_class($response->getTopScoringIntent()));
        $this->assertCount(1, $response->getEntities());
    }

    public function testLuisEntity()
    {
        $rawLuisEntity = <<<EOT
{
  "entity": "zurich",
  "type": "SwissCanton",
  "startIndex": 8,
  "endIndex": 13,
  "resolution": {
    "values": [
      "zurich"
    ]
  }
}
EOT;

        $entity = new LuisEntity(json_decode($rawLuisEntity), "I am in zurich");
        $this->assertEquals('SwissCanton', $entity->getType());
        $this->assertEquals('zurich', $entity->getEntityString());
        $this->assertEquals(8, $entity->getStartIndex());
        $this->assertEquals(13, $entity->getEndIndex());
    }

    public function testLuisIntent()
    {
        $rawIntent = <<<EOT
{
  "intent": "Define Canton",
  "score": 0.96
}
EOT;

        $intent = new LuisIntent(json_decode($rawIntent));

        $this->assertEquals('Define Canton', $intent->getLabel());
        $this->assertEquals(0.96, $intent->getConfidence());
    }
}
