<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * A UserAttribute is an piece of data that can be stored against a ChatbotUser.
 */
class UserAttribute extends Node
{
    /**
     * @var AttributeInterface
     */
    private $attribute;

    public function __construct(AttributeInterface $attribute)
    {
        parent::__construct($attribute->getId());

        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::USER_ATTRIBUTE));

        $this->addAttribute(new StringAttribute(Model::ID, $attribute->getId()));
        $this->addAttribute(new StringAttribute(Model::USER_ATTRIBUTE_TYPE, $attribute->getType()));
        $this->addAttribute(new StringAttribute(Model::USER_ATTRIBUTE_VALUE, $attribute->serialized()));

        $this->attribute = $attribute;
    }

    /**
     * @return AttributeInterface
     */
    public function getInternalAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param AttributeInterface $attribute
     */
    public function updateInternalAttribute(AttributeInterface $attribute): void
    {
        $this->setAttribute(Model::ID, $attribute->getId());
        $this->setAttribute(Model::USER_ATTRIBUTE_TYPE, $attribute->getType());
        $this->setAttribute(Model::USER_ATTRIBUTE_VALUE, $attribute->serialized());

        $this->attribute = $attribute;
    }
}
