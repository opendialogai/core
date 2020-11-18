<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\OperationEngine\Operations\EquivalenceOperation;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOperation;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOrEqualOperation;
use OpenDialogAi\OperationEngine\Operations\InSetOperation;
use OpenDialogAi\OperationEngine\Operations\IsNotSetOperation;
use OpenDialogAi\OperationEngine\Operations\IsSetOperation;
use OpenDialogAi\OperationEngine\Operations\LessThanOperation;
use OpenDialogAi\OperationEngine\Operations\LessThanOrEqualOperation;
use OpenDialogAi\OperationEngine\Operations\NotInSetOperation;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class ConditionTest extends TestCase
{
    /** @var OperationServiceInterface */
    private $operationService;

    protected function setUp() :void
    {
        parent::setUp();

        $this->operationService = app()->make(OperationServiceInterface::class);
    }

    public function testConditionEvaluationOperation()
    {
        $parameters = [ 'value' => true ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(EquivalenceOperation::$name, $attributes, $parameters);

        $this->assertTrue($condition->getEvaluationOperation() == EquivalenceOperation::$name);
    }

    public function testConditionEquivalenceComparison()
    {
        $parameters = [ 'value' => true ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(EquivalenceOperation::$name, $attributes, $parameters);

        $attributeToCompare = new BooleanAttribute('A', false);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $attributeToCompare->setValue(true);

        $this->assertTrue($operation->execute());
    }

    public function testConditionGreaterThanComparison()
    {
        $parameters = [ 'value' => 2 ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(GreaterThanOperation::$name, $attributes, $parameters);

        $attributeToCompare = new IntAttribute('A', 1);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 0 ];
        $condition = new Condition(GreaterThanOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionLessThanComparison()
    {
        $parameters = [ 'value' => 0 ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(LessThanOperation::$name, $attributes, $parameters);

        $attributeToCompare = new IntAttribute('A', 1);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 2 ];
        $condition = new Condition(LessThanOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionGreaterThanOrEqualComparison()
    {
        $parameters = [ 'value' => 1.8 ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(GreaterThanOrEqualOperation::$name, $attributes, $parameters);

        $attributeToCompare = new FloatAttribute('A', 1.5);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 1.2 ];
        $condition = new Condition(GreaterThanOrEqualOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionLessThanOrEqualComparison()
    {
        $parameters = [ 'value' => 1.2 ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(LessThanOrEqualOperation::$name, $attributes, $parameters);

        $attributeToCompare = new FloatAttribute('A', 1.5);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 1.8 ];
        $condition = new Condition(LessThanOrEqualOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionInSetComparison()
    {
        $parameters = [ 'value' => 'baz' ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(InSetOperation::$name, $attributes, $parameters);

        $attributeToCompare = new ArrayAttribute('A', ['foo', 'bar']);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 'foo' ];
        $condition = new Condition(InSetOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionNotInSetComparison()
    {
        $parameters = [ 'value' => 'foo' ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(NotInSetOperation::$name, $attributes, $parameters);

        $attributeToCompare = new ArrayAttribute('A', ['foo', 'bar']);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $parameters = [ 'value' => 'baz' ];
        $condition = new Condition(NotInSetOperation::$name, $attributes, $parameters);
        $operation->setParameters($parameters);

        $this->assertTrue($operation->execute());
    }

    public function testConditionIsSetComparison()
    {
        $parameters = [ 'value' => null ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(IsSetOperation::$name, $attributes, $parameters);

        $attributeToCompare = new StringAttribute('A', null);

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $attributeToCompare->setValue('foo');

        $this->assertTrue($operation->execute());
    }

    public function testConditionIsNotSetComparison()
    {
        $parameters = [ 'value' => null ];
        $attributes = [ 'user.name' => 'test' ];
        $condition = new Condition(IsNotSetOperation::$name, $attributes, $parameters);

        $attributeToCompare = new StringAttribute('A', 'foo');

        $operation = $this->operationService->getOperation($condition->getEvaluationOperation());
        $operation->setParameters($condition->getParameters());
        $operation->setAttributes([ 'test' => $attributeToCompare ]);

        $this->assertFalse($operation->execute());

        $attributeToCompare->setValue(null);

        $this->assertTrue($operation->execute());
    }
}
