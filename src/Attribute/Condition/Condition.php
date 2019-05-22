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

    public function __construct($evaluationOperation, $parameters = [])
    {
        $this->parameters = $parameters;
        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    public function executeOperation(AttributeInterface $attribute)
    {
        return $attribute->executeOperation($this->evaluationOperation, $this->parameters);
    }
}
