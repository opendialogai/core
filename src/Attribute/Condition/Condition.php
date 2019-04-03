<?php


namespace OpenDialogAi\Core\Attribute\Condition;

use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;

/**
 * Implementation of the Condition interface for Attributes.
 */
class Condition implements ConditionInterface
{
    use HasAttributesTrait, ConditionTrait;

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
        $conditionAttribute = $this->getAttribute($attribute->getId());
        return $conditionAttribute->compare($attribute, $this->evaluationOperation);
    }
}
