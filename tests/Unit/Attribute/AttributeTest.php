<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BasicAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\OperationEngine\Operations\EquivalenceOperation;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOrEqualOperation;

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

    public function testUnsupportedAttributeTypeException()
    {
        $this->expectException(UnsupportedAttributeTypeException::class);
        $this->expectExceptionMessage('Type unsupported is not supported');
        new BasicAttribute('a', 'unsupported', 1);
    }
}
