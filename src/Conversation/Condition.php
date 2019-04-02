<?php


namespace OpenDialogAi\Core\Conversation;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\Condition\ConditionInterface;
use OpenDialogAi\Core\Attribute\Condition\ConditionTrait;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * @see ConditionInterface
 */
class Condition extends Node implements ConditionInterface
{
    use ConditionTrait;

    public function __construct($id, AttributeInterface $attributeToCompareAgainst, $evaluationOperation)
    {
        parent::__construct($id);
        $this->attributes = new Map();
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CONDITION));

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
