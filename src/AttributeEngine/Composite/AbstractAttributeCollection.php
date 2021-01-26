<?php

namespace OpenDialogAi\AttributeEngine\Composite;

use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\AttributeEngine\Util;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;

/**
 * An abstract class that is used to populate CompositeAttributes.
 */
abstract class AbstractAttributeCollection implements AttributeCollectionInterface
{
    const SERIALISED_JSON = 'serialised_json';

    /** @var AttributeInterface[] */
    private $attributes = [];

    /**
     * Takes an input and converts it to an internal array of attributes.
     *
     * @param mixed $input
     * @param string $type
     */
    public function __construct($input, $type = self::SERIALISED_JSON)
    {
        if ($type === self::SERIALISED_JSON) {
            $attributes = $this->jsonDeserialize($input);
        } else {
            $attributes = $this->createFromInput($input, $type);
        }

        $this->attributes = $attributes;
    }

    /**
     * @inheritDoc
     */
    abstract public function toString(): string;

    /**
     * A function to create the array of Attributes from a custom input type. Each instantiation of AttributeCollection
     * should deal with the input types it would expect
     *
     * @param mixed $input The input of attributes in a custom format
     * @param string $type The type of input to inform the collection how to set itself up
     * @return AttributeInterface[]
     */
    abstract protected function createFromInput($input, $type): array;

    /**
     * @inheritDoc
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $serializedResult = [];

        if (!is_null($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $serializedResult[] = ['id' => $attribute->getId(), 'value' => $attribute->getValue()];
            }
        } else {
            $serializedResult = null;
        }

        return Util::encode($serializedResult);
    }

    /**
     * Takes an input JSON string in the following format and returns an array of AttributeInterface making use of
     * @see AttributeResolver::getAttributeFor()
     *
     * [
     *   {id: 'attribute1_id', value: 'attribute1_value'},
     *   {id: 'attribute2_id', value: 'attribute2_value'},
     *   ...
     * ]
     *
     * @param string $input A JSON Serialisation of attributes
     * @return AttributeInterface[]|null
     */
    private function jsonDeserialize($input): ?array
    {
        if (!is_null($input)) {
            $arrayOfAttributes = Util::decode($input);
            $resultAttributes = [];

            foreach ($arrayOfAttributes as $attribute) {
                $resultAttributes[] = AttributeResolver::getAttributeFor($attribute['id'], $attribute['value']);
            }
        } else {
            $resultAttributes = null;
        }

        return $resultAttributes;
    }
}
