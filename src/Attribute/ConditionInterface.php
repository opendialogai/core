<?php


namespace OpenDialogAi\Core\Attribute;

/**
 * A condition is the combination of Attribute with a predefined value and a corresponding evaluation operation.
 */
interface ConditionInterface
{
    public function compareAgainst(AttributeInterface $attribute);

    public function getEvaluationOperation();

    public function setEvaluationOperation(string $evaluationOperation);
}
