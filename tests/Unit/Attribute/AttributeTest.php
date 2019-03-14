<?php


namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BasicAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeTest extends TestCase
{

    public function testBooleanAttribute()
    {
        $a = new BooleanAttribute('testA', true);
        $b = new BooleanAttribute('testB', false);

        try {
            $this->assertFalse($a->compare($b, AbstractAttribute::EQUIVALENCE));

            $b->setValue(true);

            $this->assertTrue($a->compare($b, AbstractAttribute::EQUIVALENCE));
        } catch (UnsupportedAttributeTypeException $e) {
            self::fail('Exception thrown');
        }
    }

    public function testBooleanAttributeTypes()
    {
        $this->assertFalse((new BooleanAttribute('test', 'false'))->getValue());
    }

    public function testIntAttribute()
    {
        $a = new IntAttribute('testA', 50);
        $b = new IntAttribute('testB', 109);

        try {
            $this->assertFalse($a->compare($b, AbstractAttribute::GREATER_THAN_OR_EQUAL));

            $b->setValue(49);

            $this->assertTrue($a->compare($b, AbstractAttribute::GREATER_THAN_OR_EQUAL));
        } catch (UnsupportedAttributeTypeException $e) {
            self::fail('Exception thrown');
        }
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
