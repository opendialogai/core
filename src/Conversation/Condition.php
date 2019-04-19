<?php


namespace OpenDialogAi\Core\Conversation;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\Condition\ConditionInterface;
use OpenDialogAi\Core\Attribute\Condition\ConditionTrait;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;
use Spatie\String\Str;

/**
 * @see ConditionInterface
 */
class Condition extends Node implements ConditionInterface
{
    use ConditionTrait;

    /* @var AttributeInterface $attributeToCompareAgainst */
    private $attributeToCompareAgainst;

    public function __construct(AttributeInterface $attributeToCompareAgainst, $evaluationOperation, $id = null)
    {
        parent::__construct($id);
        $this->attributes = new Map();
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CONDITION));
        $this->addAttribute(new StringAttribute(Model::ATTRIBUTE_NAME, $attributeToCompareAgainst->getId()));
        $this->addAttribute(new StringAttribute(Model::ATTRIBUTE_VALUE, $attributeToCompareAgainst->getValue()));
        $this->addAttribute(new StringAttribute(Model::OPERATION, $evaluationOperation));

        $this->attributeToCompareAgainst = $attributeToCompareAgainst;
        $this->addAttribute($attributeToCompareAgainst);
        $this->evaluationOperation = $evaluationOperation;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttributeToCompareAgainst(): AttributeInterface
    {
        return $this->attributeToCompareAgainst;
    }


    /**
     * @param string $contextId
     */
    public function setContextId(string $contextId)
    {
        $this->addAttribute(new StringAttribute(Model::CONTEXT, $contextId));
    }

    /**
     * @return string
     */
    public function getContextId() : string
    {
        return $this->getAttribute(Model::CONTEXT)->getValue();
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
