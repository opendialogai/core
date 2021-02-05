<?php


namespace OpenDialogAi\AttributeEngine\Contracts;

/**
 * A collection attribute is made of multiple attribute values of the same type.
 */
interface CollectionAttribute extends Attribute
{

    /**
     * @param AttributeValue $value
     * @return mixed
     */
    public function addValue(AttributeValue $value);

    /**
     * @param int $index
     * @return AttributeValue
     */
    public function getValueAt(int $index): ?AttributeValue;

    /**
     * @param int $index
     * @return bool
     */
    public function removeValue(int $index): bool;

    /**
     * @return mixed
     */
    public function getValues();
}
