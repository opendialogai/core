<?php


namespace OpenDialogAi\Core\Attribute;

use Ds\Map;

/**
 * Class Condition
 * @package OpenDialog\Core\Attribute
 *
 * A condition is an Attribute with a predefined value and a corresponding evaluation operation.
 */
class Condition
{
    use HasAttributesTrait;

    // The evaluation operation to be used to compare against the attribute to compare.
    private $evaluationOperation;

    public function __construct(AttributeInterface $attributeToCompareAgainst, $evaluationOperation)
    {
        $this->attributes = new Map();

        $this->addAttribute($attributeToCompareAgainst);
        $this->evaluationOperation = $evaluationOperation;
    }

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
