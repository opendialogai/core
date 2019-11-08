<?php

namespace OpenDialogAi\Core\InterpreterEngine\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Rasa\RasaClient;
use OpenDialogAi\InterpreterEngine\Rasa\RasaIntent;
use OpenDialogAi\InterpreterEngine\Rasa\RasaResponse;

class RasaClientTest extends TestCase
{
    public function testRasaConfig()
    {
        $config = [
            'app_url' => 'app_url',
        ];

        $this->setConfigValue('opendialog.interpreter_engine.rasa_config', $config);

        $client = $this->app->make(RasaClient::class);

        $this->assertEquals(RasaClient::class, get_class($client));
    }

    public function testRasaResponse()
    {
        $rasaResponse = <<<EOT
{
  "intent": {
    "name": "initiate_search",
    "confidence": 0.6618038391
  },
  "entities": [
    {
      "entity": "GPE",
      "value": "London",
      "start": 16,
      "confidence": null,
      "end": 22,
      "extractor": "SpacyEntityExtractor"
    }
  ],
  "intent_ranking": [
    {
      "name": "initiate_search",
      "confidence": 0.6618038391
    },
    {
      "name": "ask_for_help",
      "confidence": 0.3381961609
    }
  ],
  "text": "give me info on London"
}
EOT;
        $response = new RasaResponse(json_decode($rasaResponse));

        $this->assertEquals(RasaIntent::class, get_class($response->getTopScoringIntent()));
        $this->assertCount(1, $response->getEntities());
    }

    public function testRasaIntent()
    {
        $rawIntent = <<<EOT
{
  "name": "initiate_search",
  "confidence": 0.6618038391
}
EOT;

        $intent = new RasaIntent(json_decode($rawIntent));

        $this->assertEquals('initiate_search', $intent->getLabel());
        $this->assertEquals(0.6618038391, $intent->getConfidence());
    }
}
