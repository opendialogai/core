<?php

namespace ActionEngine\Input;

use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBagInterface;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * An Action Input must be provided to all actions so that they can perform their action. It holds an
 * @see AttributeBagInterface to hold all required attributes for the action.
 */
class ActionInput
{
    /** @var AttributeBagInterface */
    public $attributeBag;

    /**
     * Sets up with an empty attribute bag
     */
    public function __construct()
    {
        $this->attributeBag = new AttributeBag();
    }

    /**
     * @return AttributeBag|AttributeBagInterface
     */
    public function getAttributeBag()
    {
        return $this->attributeBag;
    }

    /**
     * Checks whether the attribute bag contains an attribute with the given name
     *
     * @param $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName)
    {
        return $this->attributeBag->hasAttribute($attributeName);
    }

    /**
     * Adds the given attribute to the attribute bag
     *
     * @param AttributeInterface $attribute
     * @return ActionInput
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->attributeBag->addAttribute($attribute);
        return $this;
    }

    /**
     * @param AttributeBagInterface $attributeBag
     * @return ActionInput
     */
    public function setAttributeBag(AttributeBagInterface $attributeBag)
    {
        $this->attributeBag = $attributeBag;
        return $this;
    }

    /**
     * Checks if the attribute bag contains all attributes
     *
     * @param $attributeNames
     * @return bool
     */
    public function containsAllAttributes($attributeNames)
    {
        return $this->attributeBag->hasAllAttributes($attributeNames);
    }
}
