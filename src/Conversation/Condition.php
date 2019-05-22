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

    public function __construct($evaluationOperation, $attributes, $parameters = [], $id = null)
    {
        parent::__construct($id);
        $this->attributes = new Map();
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CONDITION));
        $this->addAttribute(new StringAttribute(Model::OPERATION, $evaluationOperation));

        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @param string $contextId
     */
    public function setContextId(string $contextId)
    {
        $this->addAttribute(new StringAttribute(Model::CONTEXT, $contextId));
    }

    /**
     * Gets the context id part of the condition
     *
     * @return string
     */
    public function getContextId() : string
    {
        return $this->getAttribute(Model::CONTEXT)->getValue();
    }
}
