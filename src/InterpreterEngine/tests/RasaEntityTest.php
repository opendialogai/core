<?php

namespace OpenDialogAi\Core\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Rasa\RasaEntity;

class RasaEntityTest extends TestCase
{
    public function testExtractValues()
    {
        $simpleEntity = <<<EOT
{
    "entity": "GPE",
    "value": "London",
    "start": 16,
    "confidence": null,
    "end": 22,
    "extractor": "SpacyEntityExtractor"
}
EOT;
        $simpleRASAEntity = new RasaEntity(json_decode($simpleEntity), "give me info on London");
    }
}
