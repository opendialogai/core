<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

class FloatAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attributeValue.core.float';

    public function getTypedValue()
    {
        return is_null($this->rawValue) ? null : floatval($this->rawValue);
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
