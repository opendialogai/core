<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

class StringAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attribute_value.core.string';

    public function getTypedValue()
    {
        return is_null($this->rawValue) ? null : strval($this->rawValue);
    }

    public function jsonSerialize()
    {
        return [
            static::$attributeValueType => $this->getTypedValue()->toString()
        ];
    }

    public function toString(): ?string
    {
        return (string) $this->getTypedValue();
    }
}
