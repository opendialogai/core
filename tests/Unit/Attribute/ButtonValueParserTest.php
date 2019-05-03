<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\Core\Attribute\ButtonValueParser;
use OpenDialogAi\Core\Tests\TestCase;

class ButtonValueParserTest extends TestCase
{
    public function testParseButtonValue()
    {
        $buttonValue = "test";

        $parseButtonValue = ButtonValueParser::parseButtonValue($buttonValue);
        $this->assertEquals("test", $parseButtonValue['attribute_value']);
        $this->assertEquals(ButtonValueParser::BUTTON_VALUE , $parseButtonValue['attribute_name']);

        $buttonValue = "name.value";
        $parseButtonValue = ButtonValueParser::parseButtonValue($buttonValue);
        $this->assertEquals("value", $parseButtonValue['attribute_value']);
        $this->assertEquals('name', $parseButtonValue['attribute_name']);

        $buttonValue = "name.value.broken";
        $parseButtonValue = ButtonValueParser::parseButtonValue($buttonValue);
        $this->assertEquals("name.value.broken", $parseButtonValue['attribute_value']);
        $this->assertEquals(ButtonValueParser::BUTTON_VALUE, $parseButtonValue['attribute_name']);
    }
}
