<?php

namespace OpenDialogAi\AttributeEngine\AttributeBag;

use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;

/**
 * An attribute bag holds a set of attributes that can be set or retrieved
 */
interface AttributeBagInterface
{
    /**
     * Adds an attribute to the map of attributes
     *
     * @param \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Tries to get an attribute from attributes if it exists
     *
     * @param $attributeName
     * @return \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface
     * @throws \OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException
     */
    public function getAttribute(string $attributeName): AttributeInterface;

    /**
     * Tries to get the value of the named attribute
     *
     * @param string $attributeName
     * @return mixed
     */
    public function getAttributeValue(string $attributeName);

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
