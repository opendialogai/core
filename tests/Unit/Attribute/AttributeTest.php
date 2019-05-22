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

    public function testBooleanAttribute()
    {
        /*$a = new BooleanAttribute('testA', true);
        $operation = new EquivalenceOperation();
        $parameters = [ 'value' => false ];

        try {
            $this->assertFalse($a->executeOperation($operation, $parameters));

            $parameters = [ 'value' => true ];

            $this->assertTrue($a->executeOperation($operation, $parameters));
        } catch (UnsupportedAttributeTypeException $e) {
            self::fail('Exception thrown');
        }*/
    }

    public function testBooleanAttributeTypes()
    {
        $this->assertFalse((new BooleanAttribute('test', 'false'))->getValue());
    }

    public function testIntAttribute()
    {
        /*$a = new IntAttribute('testA', 50);
        $operation = new GreaterThanOrEqualOperation();
        $parameters = [ 'value' => 109 ];

        try {
            $this->assertFalse($a->executeOperation($operation, $parameters));

            $parameters = [ 'value' => 49 ];

            $this->assertTrue($a->executeOperation($operation, $parameters));
        } catch (UnsupportedAttributeTypeException $e) {
            self::fail('Exception thrown');
        }*/
    }

    public function testBooleanToString()
    {
        $a = new BooleanAttribute('a', false);
        $this->assertTrue($a->toString() == 'false');

        $a->setValue(true);
        $this->assertTrue($a->toString() == 'true');

        $b = new IntAttribute('b', 50);
        $this->assertTrue($b->toString() == '50');
    }

    public function testUnsupportedAttributeTypeException()
    {
        $this->expectException(UnsupportedAttributeTypeException::class);
        $this->expectExceptionMessage('Type unsupported is not supported');
        new BasicAttribute('a', 'unsupported', 1);
    }
}
