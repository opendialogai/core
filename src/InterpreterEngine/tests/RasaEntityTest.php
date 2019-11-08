<?php

namespace OpenDialogAi\Core\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Rasa\RasaEntity;

class RasaEntityTest extends TestCase
{
    /**
     * @var RasaEntity
     */
    private $simpleRASAEntity;

    public function setUp(): void
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
        $this->simpleRASAEntity = new RasaEntity(json_decode($simpleEntity), "give me info on london");
    }

    public function testEntityString()
    {
        $this->assertEquals(16, $this->simpleRASAEntity->getStartIndex());
        $this->assertEquals(22, $this->simpleRASAEntity->getEndIndex());
        $this->assertEquals('london', $this->simpleRASAEntity->getEntityString());
    }

    public function testExtractValues()
    {
        $this->assertEquals('GPE', $this->simpleRASAEntity->getType());
        $this->assertCount(1, $this->simpleRASAEntity->getResolutionValues());
        $this->assertEquals('London', $this->simpleRASAEntity->getResolutionValues()[0]);
    }
}
