<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * A condition is the combination of attributes, parameters and an evaluation operation.
 */
class Condition extends Node
{
    // The evaluation operation.
    private $evaluationOperation;

    /**
     * Attributes and parameters are expected to be passed as arrays, which are then serialised and
     * stored appropriately as an ArrayAttribute.
     * @param $evaluationOperation
     * @param $attributes
     * @param array $parameters
     * @param null $id
     */
    public function __construct($evaluationOperation, $attributes, $parameters = [], $id = null)
    {
        parent::__construct($id);
        $this->attributes = new Map();
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CONDITION));
        $this->addAttribute(new StringAttribute(Model::OPERATION, $evaluationOperation));
        $this->addAttribute(new ArrayAttribute(Model::ATTRIBUTES, $attributes));
        $this->addAttribute(new ArrayAttribute(Model::PARAMETERS, $parameters));

        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @return string
     */
    public function getEvaluationOperation()
    {
        return $this->evaluationOperation;
    }

    /**
     * @param string $evaluationOperation
     */
    public function setEvaluationOperation(string $evaluationOperation)
    {
        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @return array
     */
    public function getOperationAttributes()
    {
        $attributes = $this->getAttribute(Model::ATTRIBUTES)->getValue();

        return (array) $attributes;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        $parameters = $this->getAttribute(Model::PARAMETERS)->getValue();

        return (array) $parameters;
    }
}
