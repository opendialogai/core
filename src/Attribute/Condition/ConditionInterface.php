<?php

namespace OpenDialogAi\Core\Attribute\Condition;

/**
 * A condition is the combination of Attribute with a predefined value and a corresponding evaluation operation.
 */
interface ConditionInterface
{
    public function getEvaluationOperation();

    public function setEvaluationOperation(string $evaluationOperation);

    public function getAttributes();

    public function getParameters();
}
