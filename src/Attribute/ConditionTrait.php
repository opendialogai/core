<?php


namespace OpenDialogAi\Core\Attribute;


/**
 * ConditionInterface functions implemented as a trait to enable reuse in other packages.
 */
trait ConditionTrait
{
    // The evaluation operation to be used to compare against the attribute to compare.
    private $evaluationOperation;

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    public function compareAgainst(AttributeInterface $attribute)
    {
        // Get the attribute the condition sets from the Attribute map.
        $conditionAttribute = $this->getAttribute($attribute->getId());

        return $conditionAttribute->compare($attribute, $this->evaluationOperation);
    }

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
}
