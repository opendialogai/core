<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\FloatAttributeValue;

class FloatAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $rawValue = "1.34";
        $attribute = new FloatAttribute('testFloat', new FloatAttributeValue($rawValue));
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 1.34);

        $attribute = new FloatAttribute('testFloat', $rawValue);
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 1.34);
    }

    public function testToString()
    {
        $rawValue = "1.45";
        $attribute = new FloatAttribute('test', new FloatAttributeValue($rawValue));
        $this->assertEquals("1.45", $attribute->toString());
    }
}
