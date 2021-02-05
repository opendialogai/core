<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

class BooleanAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attributeValue.core.boolean';

    public function getTypedValue()
    {
        return is_null($this->rawValue) ? null : filter_var($this->rawValue, FILTER_VALIDATE_BOOLEAN);
    }

    public function toString(): ?string
    {
        return $this->getTypedValue() ? 'true' : 'false';
    }
}
