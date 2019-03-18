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
    use HasAttributesTrait, ConditionTrait;

    public function __construct(AttributeInterface $attributeToCompareAgainst, $evaluationOperation)
    {
        $this->attributes = new Map();

        $this->addAttribute($attributeToCompareAgainst);
        $this->evaluationOperation = $evaluationOperation;
    }
}
