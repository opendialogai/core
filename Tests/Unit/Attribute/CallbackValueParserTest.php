<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\Core\Attribute\CallbackValueParser;
use OpenDialogAi\Core\Tests\TestCase;

class CallbackValueParserTest extends TestCase
{
    public function testParseCallbackValue()
    {
        $buttonValue = "test";

        $parseButtonValue = CallbackValueParser::parseCallbackValue($buttonValue);
        $this->assertEquals("test", $parseButtonValue['attribute_value']);
        $this->assertEquals(CallbackValueParser::CALLBACK_VALUE, $parseButtonValue['attribute_name']);

        $buttonValue = "name.value";
        $parseButtonValue = CallbackValueParser::parseCallbackValue($buttonValue);
        $this->assertEquals("value", $parseButtonValue['attribute_value']);
        $this->assertEquals('name', $parseButtonValue['attribute_name']);

        $buttonValue = "name.value.broken";
        $parseButtonValue = CallbackValueParser::parseCallbackValue($buttonValue);
        $this->assertEquals("name.value.broken", $parseButtonValue['attribute_value']);
        $this->assertEquals(CallbackValueParser::CALLBACK_VALUE, $parseButtonValue['attribute_name']);
    }

    public function testCallbackValueEscaped()
    {
        $buttonValue = "name.value\.escaped";
        $parseButtonValue = CallbackValueParser::parseCallbackValue($buttonValue);
        $this->assertEquals("value.escaped", $parseButtonValue['attribute_value']);
        $this->assertEquals('name', $parseButtonValue['attribute_name']);

        $buttonValue = "name.value\.e\.s\.c\.a\.p\.e\.d";
        $parseButtonValue = CallbackValueParser::parseCallbackValue($buttonValue);
        $this->assertEquals("value.e.s.c.a.p.e.d", $parseButtonValue['attribute_value']);
        $this->assertEquals('name', $parseButtonValue['attribute_name']);
    }
}
