<?php


namespace OpenDialogAi\AttributeEngine\Contracts;

use Ds\Map;

/**
 * A composite attribute is made up of a collection of attributes.
 */
interface CompositeAttribute extends Attribute
{
    /**
     * @param Attribute $attribute
     * @return mixed
     */
    public function addAttribute(Attribute $attribute);

    /**
     * @param string $id
     * @return Attribute
     */
    public function getAttribute(string $id): Attribute;

    /**
     * @param string $id
     * @return bool
     */
    public function removeAttribute(string $id): bool;

    /**
     * @return Map
     */
    public function getAttributes(): Map;
}
