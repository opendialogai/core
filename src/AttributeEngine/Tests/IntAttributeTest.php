<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\IntAttributeValue;

class IntAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $rawValue = "5";
        $attribute = new IntAttribute('testFloat', null, new IntAttributeValue($rawValue));
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 5);


        $rawValue = "15.34";
        $attribute = new IntAttribute('testFloat', $rawValue);
        $this->assertEquals($attribute->getValue(), 15);
    }

    public function testToString()
    {
        $rawValue = "14.45";
        $attribute = new IntAttribute('test', null, new IntAttributeValue($rawValue));
        $this->assertEquals("14", $attribute->toString());

        $rawValue = "1565454";
        $attribute = new IntAttribute('test', null, new IntAttributeValue($rawValue));
        $this->assertEquals("1565454", $attribute->toString());
    }
}
