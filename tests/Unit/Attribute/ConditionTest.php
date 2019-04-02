<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;;

use OpenDialogAi\Core\Attribute\Condition\Condition;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ConditionTest extends TestCase
{

    public function testConditionEvaluationOperation()
    {
        $condition = new Condition(new BooleanAttribute('A', true), AbstractAttribute::EQUIVALENCE);

        $this->assertTrue($condition->getEvaluationOperation() == AbstractAttribute::EQUIVALENCE);
    }

    public function testConditionComparison()
    {
        $condition = new Condition(new BooleanAttribute('A', true), AbstractAttribute::EQUIVALENCE);
        $attributeToCompare = new BooleanAttribute('A', false);

        $this->assertFalse($condition->compareAgainst($attributeToCompare));

        $attributeToCompare->setValue(true);

        $this->assertTrue($condition->compareAgainst($attributeToCompare));
    }
}
