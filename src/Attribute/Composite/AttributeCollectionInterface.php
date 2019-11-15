<?php

namespace OpenDialogAi\Core\Attribute\Composite;

use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * An attribute collection contains the logic for building up
 */
interface AttributeCollectionInterface extends \JsonSerializable
{
    /**
     * A custom toString method that will output the value of the attributes in a form suitable for the collection.
     *
     * @return string
     */
    public function toString(): string;

    /**
     * Loops through all attributes and returns a JSON serialisation in the format:
     * [
     *   {id: 'attribute1_id', value: 'attribute1_value'},
     *   {id: 'attribute2_id', value: 'attribute2_value'},
     *   ...
     * ]
     *
     * @return string
     */
    public function jsonSerialize();

    /**
     * Returns the attributes from this collection
     *
     * @return AttributeInterface[]
     */
    public function getAttributes() : array;
}
