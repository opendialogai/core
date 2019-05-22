<?php

namespace OpenDialogAi\Core\Attribute\Condition;

use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * Implementation of the Condition interface for Attributes.
 */
class Condition implements ConditionInterface
{
    use ConditionTrait;

    private $attributes;

    public function __construct($evaluationOperation, $attributes = [], $parameters = [])
    {
        $this->evaluationOperation = $evaluationOperation;
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
