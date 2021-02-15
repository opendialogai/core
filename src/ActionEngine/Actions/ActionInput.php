<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;

/**
 * An Action Input must be provided to all actions so that they can perform their action. It holds an
 * @see AttributeBag to hold all required attributes for the action.
 */
class ActionInput
{
    /** @var BasicAttributeBag */
    public $attributeBag;

    /**
     * Sets up with an empty attribute bag
     */
    public function __construct()
    {
        $this->attributeBag = new BasicAttributeBag();
    }

    /**
     * @return AttributeBag
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
     * @param Attribute $attribute
     * @return ActionInput
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributeBag->addAttribute($attribute);
        return $this;
    }

    /**
     * @param AttributeBag $attributeBag
     * @return ActionInput
     */
    public function setAttributeBag(AttributeBag $attributeBag)
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
