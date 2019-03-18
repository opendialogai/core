<?php


namespace OpenDialogAi\Core\Attribute;


interface ConditionInterface
{
    public function compareAgainst(AttributeInterface $attribute);

    public function getEvaluationOperation();

    public function setEvaluationOperation($evaluationOperation);
}
