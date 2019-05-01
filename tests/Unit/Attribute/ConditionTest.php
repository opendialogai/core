<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;;

use OpenDialogAi\Core\Attribute\Condition\Condition;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ConditionTest extends TestCase
{

    public function testConditionEvaluationOperation()
    {
        $condition = new Condition(new BooleanAttribute('A', true), AbstractAttribute::EQUIVALENCE);

        $this->assertTrue($condition->getEvaluationOperation() == AbstractAttribute::EQUIVALENCE);
    }

    public function testConditionEquivalenceComparison()
    {
        $condition = new Condition(new BooleanAttribute('A', true), AbstractAttribute::EQUIVALENCE);
        $attributeToCompare = new BooleanAttribute('A', false);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(true);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionGreaterThanComparison()
    {
        $condition = new Condition(new IntAttribute('A', 1), AbstractAttribute::GREATER_THAN);
        $attributeToCompare = new IntAttribute('A', 2);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(0);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionLessThanComparison()
    {
        $condition = new Condition(new IntAttribute('A', 1), AbstractAttribute::LESS_THAN);
        $attributeToCompare = new IntAttribute('A', 0);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(2);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionGreaterThanOrEqualComparison()
    {
        $condition = new Condition(new FloatAttribute('A', 1.5), AbstractAttribute::GREATER_THAN);
        $attributeToCompare = new FloatAttribute('A', 1.8);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(1.2);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionLessThanOrEqualComparison()
    {
        $condition = new Condition(new FloatAttribute('A', 1.5), AbstractAttribute::LESS_THAN);
        $attributeToCompare = new FloatAttribute('A', 1.2);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(1.8);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionInSetComparison()
    {
        $condition = new Condition(new StringAttribute('A', ['foo', 'bar']), AbstractAttribute::IN_SET);
        $attributeToCompare = new StringAttribute('A', 'baz');

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue('foo');

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionNotInSetComparison()
    {
        $condition = new Condition(new StringAttribute('A', ['foo', 'bar']), AbstractAttribute::NOT_IN_SET);
        $attributeToCompare = new StringAttribute('A', 'foo');

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue('baz');

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionIsSetComparison()
    {
        $condition = new Condition(new StringAttribute('A', 'true'), AbstractAttribute::IS_SET);
        $attributeToCompare = new StringAttribute('A', null);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue('foo');

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }

    public function testConditionIsNotSetComparison()
    {
        $condition = new Condition(new StringAttribute('A', 'false'), AbstractAttribute::IS_SET);
        $attributeToCompare = new StringAttribute('A', 'foo');

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(null);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }
}
