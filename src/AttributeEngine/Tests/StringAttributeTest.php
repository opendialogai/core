<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;

class StringAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $rawValue = "hello";
        $attribute = new StringAttribute('testFloat', new StringAttributeValue($rawValue));
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 'hello');

        $fromRawAttribute = new StringAttribute('testFloat', $rawValue);
        $this->assertEquals($fromRawAttribute->getAttributeValue()->getTypedValue(), 'hello');
    }

    public function testToString()
    {
        $rawValue = "goodbye";
        $attribute = new StringAttribute('test', new StringAttributeValue($rawValue));
        $this->assertEquals("goodbye", $attribute->toString());
    }
}
