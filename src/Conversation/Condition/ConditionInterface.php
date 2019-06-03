<?php

namespace OpenDialogAi\Core\Conversation\Condition;

/**
 * A condition is the combination of attributes, parameters and an evaluation operation.
 */
interface ConditionInterface
{
    public function getEvaluationOperation();

    public function setEvaluationOperation(string $evaluationOperation);

    public function getOperationAttributes();

    public function getParameters();
}
