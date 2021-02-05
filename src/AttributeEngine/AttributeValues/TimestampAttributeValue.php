<?php


namespace OpenDialogAi\AttributeEngine\AttributeValues;

use Carbon\Carbon;

class TimestampAttributeValue extends AbstractAttributeValue
{
    public static $attributeValueType = 'attributeValue.core.timestamp';

    public function getTypedValue()
    {
        return is_null($this->rawValue) ? null : filter_var($this->rawValue, FILTER_VALIDATE_INT);
    }

    public function jsonSerialize()
    {
        return [
            static::$attributeValueType => $this->getTypedValue()->toString()
        ];
    }

    public function toString(): ?string
    {
        return Carbon::createFromTimestamp($this->getTypedValue())->toIso8601String();
    }
}
