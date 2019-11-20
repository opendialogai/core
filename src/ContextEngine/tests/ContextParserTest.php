<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextParserTest extends TestCase
{
    public $setupWithDGraphInit = false;

    public function testDetermineContextAndAttributeId()
    {
        $parsedAttribute = ContextParser::parseAttributeName("user.name");
        $this->assertEquals("user", $parsedAttribute->contextId);
        $this->assertEquals("name", $parsedAttribute->attributeId);

        $parsedAttribute = ContextParser::parseAttributeName("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $parsedAttribute->contextId);
        $this->assertEquals("name", $parsedAttribute->attributeId);

        $parsedAttribute = ContextParser::parseAttributeName("user.last.name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $parsedAttribute->contextId);
        $this->assertEquals(AbstractAttribute::INVALID_ATTRIBUTE_NAME, $parsedAttribute->attributeId);
    }

    public function testDetermineContextId()
    {
        $contextId = ContextParser::determineContextId("user.name");
        $this->assertEquals("user", $contextId);

        $contextId = ContextParser::determineContextId("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);

        $contextId = ContextParser::determineContextId("user.last.name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);
    }

    public function testDetermineAttributeId()
    {
        $attributeId = ContextParser::determineAttributeId("user.name");
        $this->assertEquals("name", $attributeId);

        $attributeId = ContextParser::determineAttributeId("name");
        $this->assertEquals("name", $attributeId);

        $attributeId = ContextParser::determineAttributeId("user.last.name");
        $this->assertEquals(AbstractAttribute::INVALID_ATTRIBUTE_NAME, $attributeId);
    }

    public function testArrayNotationAttribute()
    {
        $attribute = "user.test[1][name]";

        $this->assertEquals('user', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertEquals(1, ContextParser::parseAttributeName($attribute)->getAccessor()[0]);
        $this->assertEquals('name', ContextParser::parseAttributeName($attribute)->getAccessor()[1]);

        $attribute = "user.test[1]";

        $this->assertEquals('user', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertEquals(1, ContextParser::parseAttributeName($attribute)->getAccessor()[0]);
        $this->assertNull(ContextParser::parseAttributeName($attribute)->itemName);

        // We do not support this depth
        $attribute = "user.test[1][test][test]";

        $this->assertEquals('user', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertNull(ContextParser::parseAttributeName($attribute)->itemNumber);
        $this->assertNull(ContextParser::parseAttributeName($attribute)->itemName);
    }
}
