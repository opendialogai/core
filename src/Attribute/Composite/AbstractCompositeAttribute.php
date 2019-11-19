<?php

namespace OpenDialogAi\Core\Attribute\Composite;

use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * A composite attribute is one that contains a number of attributes within
 */
abstract class AbstractCompositeAttribute extends AbstractAttribute
{
    public static $type = 'attribute.core.collection';

    /** @var string The class name of the AttributeCollection to use */
    protected $attributeCollectionType;

    /** @var AttributeCollectionInterface */
    protected $attributeCollection;

    public function __construct($id, $value)
    {
        parent::__construct($id, []);
        $this->setValue($value);
    }

    /**
     * Sets the value as a JSON encoding of attribute collection.
     *
     * + If $value is a instantiation of $this->attributeCollectionType, use it directly
     * + Otherwise, create a new attribute collection passing in $value
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if (gettype($value) === "object" && get_class($value) == $this->attributeCollectionType) {
            $this->attributeCollection = $value;
        } else {
            $this->attributeCollection = new $this->attributeCollectionType($value);
        }

        $this->value =  htmlspecialchars(json_encode($this->attributeCollection), ENT_QUOTES);
    }

    /**
     * Returns the array of attributes
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->attributeCollection->jsonSerialize();
    }

    /**
     * Returns the value of the toString method of the attribute collection
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
