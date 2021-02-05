<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;

class StringAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $rawValue = "hello";
        $attribute = new StringAttribute('testFloat', null, new StringAttributeValue($rawValue));
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 'hello');
    }

    public function testToString()
    {
        $rawValue = "goodbye";
        $attribute = new StringAttribute('test', null, new StringAttributeValue($rawValue));
        $this->assertEquals("goodbye", $attribute->toString());
    }
}
