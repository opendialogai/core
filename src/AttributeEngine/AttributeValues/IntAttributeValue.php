<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

class IntAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attributeValue.core.int';

    public function getTypedValue()
    {
        return is_null($this->rawValue) ? null : intval($this->rawValue);
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
