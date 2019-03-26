<?php

namespace OpenDialogAi\Core\Attribute\AttributeBag;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Exceptions\AttributeBagAttributeDoesNotExist;

/**
 * An attribute bag holds a set of attributes that can be set or retrieved
 */
interface AttributeBagInterface
{
    /**
     * Adds an attribute to the map of attributes
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute) : void;

    /**
     * Tries to get an attribute from attributes if it exists
     *
     * @param $attributeName
     * @return AttributeInterface
     * @throws AttributeBagAttributeDoesNotExist
     */
    public function getAttribute($attributeName) : AttributeInterface;

    /**
     * Checks whether the attribute with given name exists
     *
     * @param $attributeName string
     * @return bool
     */
    public function hasAttribute($attributeName): bool;

    /**
     * Checks if the attribute bag contains all of the given attributes by name
     *
     * @param $attributeNames string[] An array of attribute names to check
     * @return bool
     */
    public function hasAllAttributes($attributeNames): bool;
}
