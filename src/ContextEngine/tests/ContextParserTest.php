<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextParserTest extends TestCase
{
    public function testDetermineContext()
    {
        list($contextId, $attributeId) = ContextParser::determineContext("user.name");
        $this->assertEquals("user", $contextId);
        $this->assertEquals("name", $attributeId);

        list($contextId, $attributeId) = ContextParser::determineContext("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);
        $this->assertEquals("name", $attributeId);
    }
}

