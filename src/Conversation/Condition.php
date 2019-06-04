<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
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
        $attributes = json_decode(htmlspecialchars_decode($attributes));

        return (array) $attributes;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        $parameters = $this->getAttribute(Model::PARAMETERS)->getValue();
        $parameters = json_decode(htmlspecialchars_decode($parameters));

        return (array) $parameters;
    }
}
