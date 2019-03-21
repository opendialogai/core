<?php


namespace OpenDialogAi\Core\Conversation;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\ConditionInterface;
use OpenDialogAi\Core\Attribute\ConditionTrait;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * @see ConditionInterface
 */
class Condition extends Node implements ConditionInterface
{
    use ConditionTrait;

    public function __construct(AttributeInterface $attributeToCompareAgainst, $evaluationOperation)
    {
        parent::__construct();
        $this->attributes = new Map();

        $this->addAttribute($attributeToCompareAgainst);
        $this->evaluationOperation = $evaluationOperation;
    }
}
