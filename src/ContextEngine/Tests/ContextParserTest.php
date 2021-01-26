<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Tests\TestCase;

class ContextParserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetermineContextAndAttributeId()
    {
        $parsedAttribute = ContextParser::parseAttributeName("session.name");
        $this->assertEquals("session", $parsedAttribute->contextId);
        $this->assertEquals("name", $parsedAttribute->attributeId);

        $parsedAttribute = ContextParser::parseAttributeName("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $parsedAttribute->contextId);
        $this->assertEquals("name", $parsedAttribute->attributeId);

        $parsedAttribute = ContextParser::parseAttributeName("user.name.last");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $parsedAttribute->contextId);
        $this->assertEquals('name', $parsedAttribute->attributeId);
    }

    public function testDetermineContextId()
    {
        $contextId = ContextParser::determineContextId("session.name");
        $this->assertEquals("session", $contextId);

        $contextId = ContextParser::determineContextId("name");
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $contextId);

        $contextId = ContextParser::determineContextId("session.name.last");
        $this->assertEquals('session', $contextId);
    }

    public function testDetermineAttributeId()
    {
        $attributeId = ContextParser::determineAttributeId("session.name");
        $this->assertEquals("name", $attributeId);

        $attributeId = ContextParser::determineAttributeId("name");
        $this->assertEquals("name", $attributeId);

        $attributeId = ContextParser::determineAttributeId("session.name.last");
        $this->assertEquals('name', $attributeId);

        $attributeId = ContextParser::determineAttributeId("name.last");
        $this->assertEquals('last', $attributeId);
    }

    public function testArrayNotationAttribute()
    {
        $attribute = "session.test.1.name";

        $this->assertEquals('session', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertEquals(1, ContextParser::parseAttributeName($attribute)->getAccessor()[0]);
        $this->assertEquals('name', ContextParser::parseAttributeName($attribute)->getAccessor()[1]);

        $attribute = "session.test.1";

        $this->assertEquals('session', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertEquals([1], ContextParser::parseAttributeName($attribute)->getAccessor());

        $attribute = "session.test.1.test.test";
        $this->assertEquals('session', ContextParser::parseAttributeName($attribute)->contextId);
        $this->assertEquals('test', ContextParser::parseAttributeName($attribute)->attributeId);
        $this->assertEquals([1, 'test', 'test'], ContextParser::parseAttributeName($attribute)->getAccessor());
    }
}
