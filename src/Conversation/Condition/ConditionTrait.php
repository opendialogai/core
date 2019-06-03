<?php

namespace OpenDialogAi\Core\Conversation\Condition;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Model;

/**
 * ConditionInterface functions implemented as a trait to enable reuse in other packages.
 */
trait ConditionTrait
{
    // The evaluation operation.
    private $evaluationOperation;

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
