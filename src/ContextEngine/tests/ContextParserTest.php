<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextParserTest extends TestCase
{
    public function testDetermineContextAndAttributeId()
    {
        list($contextId, $attributeId) = ContextParser::determineContextAndAttributeId("user.name");
        $this->assertEquals("user", $contextId);
        $this->assertEquals("name", $attributeId);

        list($contextId, $attributeId) = ContextParser::determineContextAndAttributeId("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);
        $this->assertEquals("name", $attributeId);
    }

    public function testDetermineContextId()
    {
        $contextId = ContextParser::determineContextId("user.name");
        $this->assertEquals("user", $contextId);

        $contextId = ContextParser::determineContextId("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);
    }

    public function testDetermineAttributeId()
    {
        $attributeId = ContextParser::determineAttributeId("user.name");
        $this->assertEquals("name", $attributeId);

        $attributeId = ContextParser::determineAttributeId("name");
        $this->assertEquals("name", $attributeId);
    }
}

