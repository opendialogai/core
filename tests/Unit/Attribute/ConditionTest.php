<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;;

use OpenDialogAi\Core\Attribute\Condition\Condition;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\Operation\EquivalenceOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\InSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsNotSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsSetOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\NotInSetOperation;
use OpenDialogAi\Core\Tests\TestCase;

class ConditionTest extends TestCase
{
    public function testConditionEvaluationOperation()
    {
        $parameters = [ 'value' => true ];
        $condition = new Condition(EquivalenceOperation::NAME, $parameters);

        $this->assertTrue($condition->getEvaluationOperation()::NAME == EquivalenceOperation::NAME);
    }

    public function testConditionEquivalenceComparison()
    {
        $parameters = [ 'value' => true ];
        $condition = new Condition(EquivalenceOperation::NAME, $parameters);
        $attributeToCompare = new BooleanAttribute('A', false);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $attributeToCompare->setValue(true);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionGreaterThanComparison()
    {
        $parameters = [ 'value' => 2 ];
        $condition = new Condition(GreaterThanOperation::NAME, $parameters);
        $attributeToCompare = new IntAttribute('A', 1);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 0 ];
        $condition = new Condition(GreaterThanOperation::NAME, $parameters);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionLessThanComparison()
    {
        $parameters = [ 'value' => 0 ];
        $condition = new Condition(LessThanOperation::NAME, $parameters);
        $attributeToCompare = new IntAttribute('A', 1);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 2 ];
        $condition = new Condition(LessThanOperation::NAME, $parameters);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionGreaterThanOrEqualComparison()
    {
        $parameters = [ 'value' => 1.8 ];
        $condition = new Condition(GreaterThanOrEqualOperation::NAME, $parameters);
        $attributeToCompare = new FloatAttribute('A', 1.5);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 1.2 ];
        $condition = new Condition(GreaterThanOrEqualOperation::NAME, $parameters);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionLessThanOrEqualComparison()
    {
        $parameters = [ 'value' => 1.2 ];
        $condition = new Condition(LessThanOrEqualOperation::NAME, $parameters);
        $attributeToCompare = new FloatAttribute('A', 1.5);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 1.8 ];
        $condition = new Condition(LessThanOrEqualOperation::NAME, $parameters);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionInSetComparison()
    {
        $parameters = [ 'value' => 'baz' ];
        $condition = new Condition(InSetOperation::NAME, $parameters);
        $attributeToCompare = new StringAttribute('A', ['foo', 'bar']);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 'foo' ];
        $condition = new Condition(InSetOperation::NAME, $parameters);
        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionNotInSetComparison()
    {
        $parameters = [ 'value' => 'foo' ];
        $condition = new Condition(NotInSetOperation::NAME, $parameters);
        $attributeToCompare = new StringAttribute('A', ['foo', 'bar']);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $parameters = [ 'value' => 'baz' ];
        $condition = new Condition(NotInSetOperation::NAME, $parameters);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionIsSetComparison()
    {
        $parameters = [ 'value' => null ];
        $condition = new Condition(IsSetOperation::NAME, $parameters);
        $attributeToCompare = new StringAttribute('A', null);

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $attributeToCompare->setValue('foo');

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }

    public function testConditionIsNotSetComparison()
    {
        $parameters = [ 'value' => null ];
        $condition = new Condition(IsNotSetOperation::NAME, $parameters);
        $attributeToCompare = new StringAttribute('A', 'foo');

        $this->assertFalse($condition->executeOperation($attributeToCompare));

        $attributeToCompare->setValue(null);

        $this->assertTrue($condition->executeOperation($attributeToCompare));
    }
}
