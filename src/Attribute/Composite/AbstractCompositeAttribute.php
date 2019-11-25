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
    /**
     * @var string
     */
    public static $type = 'attribute.core.collection';

    /** @var string The class name of the AttributeCollection to use */
    protected $attributeCollectionType;

    /** @var AttributeCollectionInterface */
    protected $attributeCollection;

    /**
     * AbstractCompositeAttribute constructor.
     *
     * @param $id
     * @param $value
     */
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

        $this->value = $this->attributeCollection->jsonSerialize();
    }

    /**
     * Returns the value of the given index. If no index is provided, returns all attributes in the attribute collection.
     *
     * @param array $index
     * @return mixed | AttributeInterface[]
     */
    public function getValue(array $index = [])
    {
        if (!$index) {
            return $this->attributeCollection->getAttributes();
        }

        return $this->getValueRecursive($index, $this->attributeCollection->getAttributes(), 0);
    }

    /**
     * Recursive function that loops through all attributes in the CompositeAttribute and pulls out the request value
     * based on the index array.
     *
     * @param $index
     * @param $attributes
     * @param $count
     * @return mixed
     */
    private function getValueRecursive($index, $attributes, $count)
    {
        if (array_key_exists($count, $index)) {
            $search = $index[$count];

            if (is_array($attributes)) {
                $attributes = array_reduce(array_keys($attributes), function ($carry, $key) use ($attributes, $search) {
                    if ($search === $key) {
                        return $attributes[$key];
                    }

                    if ($attributes[$key] instanceof AttributeInterface && $attributes[$key]->getId() === $search) {
                        return $attributes[$key];
                    }

                    return $carry;
                });
            } elseif ($attributes instanceof AbstractAttribute) {
                $attributes = $attributes->getValue([$search]);
            } else {
                Log::warning("Couldn't recognize attribute type in AbstractCompositeAttribute.");
            }

            return $this->getValueRecursive($index, $attributes, $count + 1);
        }

        if (is_null($attributes)) {
            Log::warning(sprintf(
                'Unable to extract requested index from attribute - %s - %s',
                get_class($this),
                json_encode($index)
            ));
        }

        return $attributes;
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

    /**
     * @inheritDoc
     */
    public function serialized(): string
    {
        return $this->value;
    }
}
