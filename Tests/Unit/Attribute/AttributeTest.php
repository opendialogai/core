<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
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

        $a->setRawValue(true);
        $this->assertEquals($a->toString(), 'true');

        $b = new IntAttribute('b', 50);
        $this->assertEquals($b->toString(), '50');
    }
}
