<?php

namespace OpenDialogAi\Core\Attribute\Composite;

use Illuminate\Support\Facades\Log;
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

        $this->value = json_encode($this->attributeCollection);
    }

    /**
     * Returns the array of attributes
     * @param array $index
     * @return AttributeInterface[]
     */
    public function getValue(array $index = [])
    {
        if (!$index) {
            return $this->attributeCollection->getAttributes();
        }

        $attributes = $this->attributeCollection->getAttributes();

        $useColsure = function ($index, $attributes, $count) use (&$useColsure) {
            if (array_key_exists($count, $index)) {
                $search = $index[$count];

                if (is_array($attributes)) {
                    $attributes = array_reduce($attributes, function ($carry, $attribute) use ($search) {
                        if ($attribute->getId() === $search) {
                            $carry = $attribute;
                        }
                        return $carry;
                    });
                } elseif ($attributes instanceof AbstractAttribute) {
                    $attributes = $attributes->getValue([$search]);
                } else {
                    Log::warning("Couldn't recognize attribute type in AbstractCompositeAttribute.");
                    $attributes = null;
                }

                $result = $useColsure($index, $attributes, $count+1);

                return $result;
            } else {
                return $attributes;
            }
        };

        return $useColsure($index, $attributes, 0);
    }

    /**
     * Returns the value of the toString method of the attribute collection
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->attributeCollection->toString();
    }
}
