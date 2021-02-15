<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

class SerializedArrayAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attribute_value.core.serialized_array';

    public function setRawValue($rawValue)
    {
        $this->rawValue = serialize($rawValue);
    }

    public function getTypedValue()
    {
        return unserialize($this->getRawValue());
    }

    public function toString(): ?string
    {
        return implode(':', $this->getTypedValue());
    }
}
