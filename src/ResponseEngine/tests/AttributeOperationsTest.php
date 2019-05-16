<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\TimestampAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeOperationsTest extends TestCase
{
    public function testBooleanOperations()
    {
        // Equality.
        $a1 = new BooleanAttribute('test', true);
        $a2 = new BooleanAttribute('test', true);
        $this->assertSame(true, $a1->compare($a2, 'eq'));
        $a2 = new BooleanAttribute('test', false);
        $this->assertSame(false, $a1->compare($a2, 'eq'));
    }

    public function testFloatOperations()
    {
        // Equality.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(true, $a1->compare($a2, 'eq'));
        $a2 = new FloatAttribute('test', 2.0);
        $this->assertSame(false, $a1->compare($a2, 'eq'));

        // Greater than or equal.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new FloatAttribute('test', 1.5);
        $this->assertSame(false, $a1->compare($a2, 'gte'));

        // Greater than.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(false, $a1->compare($a2, 'gt'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(true, $a1->compare($a2, 'gt'));
        $a2 = new FloatAttribute('test', 1.5);
        $this->assertSame(false, $a1->compare($a2, 'gt'));

        // Less than or equal.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(true, $a1->compare($a2, 'lte'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(false, $a1->compare($a2, 'lte'));
        $a2 = new FloatAttribute('test', 1.5);
        $this->assertSame(true, $a1->compare($a2, 'lte'));

        // Less than.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new FloatAttribute('test', 1.5);
        $this->assertSame(true, $a1->compare($a2, 'lt'));

        // In set.
        $a1 = new FloatAttribute('test', [1.0, 2.0]);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(true, $a1->compare($a2, 'in_set'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(false, $a1->compare($a2, 'in_set'));

        // Not in set.
        $a1 = new FloatAttribute('test', [1.0, 2.0]);
        $a2 = new FloatAttribute('test', 1.0);
        $this->assertSame(false, $a1->compare($a2, 'not_in_set'));
        $a2 = new FloatAttribute('test', 0.5);
        $this->assertSame(true, $a1->compare($a2, 'not_in_set'));

        // Is set.
        $a1 = new FloatAttribute('test', 1.0);
        $a2 = new FloatAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_set'));

        // Is not set.
        $a1 = new FloatAttribute('test', null);
        $a2 = new FloatAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_not_set'));
    }

    public function testIntOperations()
    {
        // Equality.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(true, $a1->compare($a2, 'eq'));
        $a2 = new IntAttribute('test', 2);
        $this->assertSame(false, $a1->compare($a2, 'eq'));

        // Greater than or equal.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new IntAttribute('test', 2);
        $this->assertSame(false, $a1->compare($a2, 'gte'));

        // Greater than.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(false, $a1->compare($a2, 'gt'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(true, $a1->compare($a2, 'gt'));
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(false, $a1->compare($a2, 'gt'));

        // Less than or equal.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(true, $a1->compare($a2, 'lte'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(false, $a1->compare($a2, 'lte'));
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(true, $a1->compare($a2, 'lte'));

        // Less than.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new IntAttribute('test', 2);
        $this->assertSame(true, $a1->compare($a2, 'lt'));

        // In set.
        $a1 = new IntAttribute('test', [1, 2]);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(true, $a1->compare($a2, 'in_set'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(false, $a1->compare($a2, 'in_set'));

        // Not in set.
        $a1 = new IntAttribute('test', [1, 2]);
        $a2 = new IntAttribute('test', 1);
        $this->assertSame(false, $a1->compare($a2, 'not_in_set'));
        $a2 = new IntAttribute('test', 0);
        $this->assertSame(true, $a1->compare($a2, 'not_in_set'));

        // Is set.
        $a1 = new IntAttribute('test', 1);
        $a2 = new IntAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_set'));

        // Is not set.
        $a1 = new IntAttribute('test', null);
        $a2 = new IntAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_not_set'));
    }

    public function testStringOperations()
    {
        // Equality.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(true, $a1->compare($a2, 'eq'));
        $a2 = new StringAttribute('test', 'bar');
        $this->assertSame(false, $a1->compare($a2, 'eq'));

        // Greater than or equal.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new StringAttribute('test', 'foa');
        $this->assertSame(true, $a1->compare($a2, 'gte'));
        $a2 = new StringAttribute('test', 'foz');
        $this->assertSame(false, $a1->compare($a2, 'gte'));

        // Greater than.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(false, $a1->compare($a2, 'gt'));
        $a2 = new StringAttribute('test', 'foa');
        $this->assertSame(true, $a1->compare($a2, 'gt'));
        $a2 = new StringAttribute('test', 'foz');
        $this->assertSame(false, $a1->compare($a2, 'gt'));

        // Less than or equal.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(true, $a1->compare($a2, 'lte'));
        $a2 = new StringAttribute('test', 'foa');
        $this->assertSame(false, $a1->compare($a2, 'lte'));
        $a2 = new StringAttribute('test', 'foz');
        $this->assertSame(true, $a1->compare($a2, 'lte'));

        // Less than.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new StringAttribute('test', 'foa');
        $this->assertSame(false, $a1->compare($a2, 'lt'));
        $a2 = new StringAttribute('test', 'foz');
        $this->assertSame(true, $a1->compare($a2, 'lt'));

        // In set.
        $a1 = new StringAttribute('test', ['foo', 'bar']);
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(true, $a1->compare($a2, 'in_set'));
        $a2 = new StringAttribute('test', 'baz');
        $this->assertSame(false, $a1->compare($a2, 'in_set'));

        // Not in set.
        $a1 = new StringAttribute('test', ['foo', 'bar']);
        $a2 = new StringAttribute('test', 'foo');
        $this->assertSame(false, $a1->compare($a2, 'not_in_set'));
        $a2 = new StringAttribute('test', 'baz');
        $this->assertSame(true, $a1->compare($a2, 'not_in_set'));

        // Is set.
        $a1 = new StringAttribute('test', 'foo');
        $a2 = new StringAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_set'));

        // Is not set.
        $a1 = new StringAttribute('test', null);
        $a2 = new StringAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_not_set'));
    }

    public function testTimestampOperations()
    {
        // Time passed equals.
        $a1 = new TimestampAttribute('test', 0);
        $a2 = new TimestampAttribute('test', now()->timestamp);
        $this->assertSame(true, $a1->compare($a2, 'time_passed_equals'));
        $a2 = new TimestampAttribute('test', now()->timestamp + 10);
        $this->assertSame(false, $a1->compare($a2, 'time_passed_equals'));

        // Time passed greater than.
        $a1 = new TimestampAttribute('test', 1000);
        $a2 = new TimestampAttribute('test', 500);
        $this->assertSame(true, $a1->compare($a2, 'time_passed_greater_than'));
        $a2 = new TimestampAttribute('test', 1500);
        $this->assertSame(true, $a1->compare($a2, 'time_passed_greater_than'));

        // Time passed less than.
        $a1 = new TimestampAttribute('test', 500);
        $a2 = new TimestampAttribute('test', now()->timestamp + 750);
        $this->assertSame(true, $a1->compare($a2, 'time_passed_less_than'));
        $a2 = new TimestampAttribute('test', now()->timestamp + 250);
        $this->assertSame(true, $a1->compare($a2, 'time_passed_less_than'));

        // Is set.
        $a1 = new TimestampAttribute('test', 1);
        $a2 = new TimestampAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_set'));

        // Is not set.
        $a1 = new TimestampAttribute('test', null);
        $a2 = new TimestampAttribute('test', null);
        $this->assertSame(true, $a1->compare($a2, 'is_not_set'));
    }
}
