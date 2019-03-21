<?php


namespace OpenDialogAi\Core\Attribute;

use Ds\Map;

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
}
