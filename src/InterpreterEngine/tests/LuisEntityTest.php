<?php

namespace OpenDialogAi\Core\InterpreterEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Luis\LuisEntity;

class LuisEntityTest extends TestCase
{
    public function testExtractValues()
    {
        $simpleEntity = <<<EOT
{
    "entity": "LGW",
    "type": "airport",
    "startIndex": 20,
    "endIndex": 22
}
EOT;
        $simpleLUISEntity = new LuisEntity(json_decode($simpleEntity), "Book me a flight to LGW");
        $this->assertEquals(1, count($simpleLUISEntity->getResolutionValues()));
        $this->assertEquals("LGW", $simpleLUISEntity->getResolutionValues()[0]);

        $simpleEntityWithCaseSensitivity = <<<EOT
{
    "entity": "john",
    "type": "built.personName",
    "startIndex": 0,
    "endIndex": 3
}
EOT;
        $simpleLUISEntity = new LuisEntity(json_decode($simpleEntityWithCaseSensitivity), "John");
        $this->assertEquals(1, count($simpleLUISEntity->getResolutionValues()));
        $this->assertEquals("John", $simpleLUISEntity->getResolutionValues()[0]);

        $prebuiltEntity = <<<EOT
{
    "entity": "test@example.com",
    "type": "builtin.email",
    "startIndex": 12,
    "endIndex": 27,
    "resolution": {
        "value": "test@example.com"
    }
}
EOT;
        $prebuiltLUISEntity = new LuisEntity(json_decode($prebuiltEntity), "My email is test@example.com");
        $this->assertEquals(1, count($prebuiltLUISEntity->getResolutionValues()));
        $this->assertEquals("test@example.com", $prebuiltLUISEntity->getResolutionValues()[0]);

        $listEntity = <<<EOT
{
    "entity": "six foot",
    "type": "height",
    "startIndex": 5,
    "endIndex": 12,
    "resolution": {
        "values": [
            "tall"
        ]
    }
}
EOT;
        $listLUISEntity = new LuisEntity(json_decode($listEntity), "I am six foot tall");
        $this->assertEquals(1, count($listLUISEntity->getResolutionValues()));
        $this->assertEquals("tall", $listLUISEntity->getResolutionValues()[0]);

        $listEntityWithManyResolutions = <<<EOT
{
    "entity": "something ambiguous",
    "type": "some_type",
    "startIndex": 0,
    "endIndex": 18,
    "resolution": {
        "values": [
            "value_1",
            "value_2",
            "value_3"
        ]
    }
}
EOT;
        $listLUISEntityWithManyResolutions = new LuisEntity(json_decode($listEntityWithManyResolutions), "something ambiguous");
        $this->assertEquals(3, count($listLUISEntityWithManyResolutions->getResolutionValues()));
        $this->assertEquals("value_1", $listLUISEntityWithManyResolutions->getResolutionValues()[0]);
        $this->assertEquals("value_2", $listLUISEntityWithManyResolutions->getResolutionValues()[1]);
        $this->assertEquals("value_3", $listLUISEntityWithManyResolutions->getResolutionValues()[2]);
    }
}
