<?php


namespace OpenDialogAi\Core\Attribute;


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
     * @return mixed
     */
    public function getEvaluationOperation()
    {
        return $this->evaluationOperation;
    }

    /**
     * @param $evaluationOperation
     */
    public function setEvaluationOperation($evaluationOperation)
    {
        $this->evaluationOperation = $evaluationOperation;
    }
}
