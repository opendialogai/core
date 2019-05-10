<?php


namespace OpenDialogAi\Core\Attribute\Condition;


use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * ConditionInterface functions implemented as a trait to enable reuse in other packages.
 */
trait ConditionTrait
{
    // The evaluation operation to be used to compare against the attribute to compare.
    private $evaluationOperation;

    private $parameters;

    /**
     * @return string
     */
    public function getEvaluationOperation()
    {
        return $this->evaluationOperation;
    }

    /**
     * @param string $evaluationOperation
     */
    public function setEvaluationOperation(string $evaluationOperation)
    {
        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
