<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\AttributeEngine\ArrayAttribute;
use OpenDialogAi\AttributeEngine\BooleanAttribute;
use OpenDialogAi\AttributeEngine\IntAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeTest extends TestCase
{
    public function testBooleanAttributeTypes()
    {
        $this->assertFalse((new BooleanAttribute('test', 'false'))->getValue());
    }

    public function testBooleanToString()
    {
        $a = new BooleanAttribute('a', false);
        $this->assertEquals($a->toString(), 'false');

        $a->setValue(true);
        $this->assertEquals($a->toString(), 'true');

        $b = new IntAttribute('b', 50);
        $this->assertEquals($b->toString(), '50');
    }

    public function testArrayAttribute()
    {
        $array = [
            'first' => ['one', 'two'],
            'second' => ['three', 'four']
        ];

        $attribute = new ArrayAttribute('test', $array);

        $this->assertEquals('one', $attribute->getValue(['first', 0]));
    }
}
