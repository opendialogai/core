<?php

namespace OpenDialogAi\Core\Conversation\Condition;

use Ds\Map;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Model;
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
        $this->addAttribute(new StringAttribute(Model::ATTRIBUTES, htmlspecialchars(json_encode($attributes), ENT_QUOTES)));
        $this->addAttribute(new StringAttribute(Model::PARAMETERS, htmlspecialchars(json_encode($parameters), ENT_QUOTES)));

        $this->evaluationOperation = $evaluationOperation;
    }
}
