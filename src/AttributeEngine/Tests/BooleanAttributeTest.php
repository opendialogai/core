<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\BooleanAttributeValue;

class BooleanAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testRawValueSetting()
    {
        $rawValue = "false";
        $attribute = new BooleanAttribute('test', new BooleanAttributeValue($rawValue));
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue(0);
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue('0');
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue('');
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue('something');
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue(null);
        $this->assertNull($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue('true');
        $this->assertTrue($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue(1);
        $this->assertTrue($attribute->getAttributeValue()->getTypedValue());

        $attribute->getAttributeValue()->setRawValue('1');
        $this->assertTrue($attribute->getAttributeValue()->getTypedValue());
    }

    public function testRawValueConstructor()
    {
        $rawValue = "false";
        $attribute = new BooleanAttribute('test', $rawValue);
        $this->assertFalse($attribute->getValue());
        $this->assertFalse($attribute->getAttributeValue()->getTypedValue());
    }

    public function testToString()
    {
        $rawValue = "false";
        $attribute = new BooleanAttribute('test', new BooleanAttributeValue($rawValue));
        $this->assertEquals("false", $attribute->toString());

        $rawValue = "true";
        $attribute = new BooleanAttribute('test', new BooleanAttributeValue($rawValue));
        $this->assertEquals("true", $attribute->toString());
    }
}
