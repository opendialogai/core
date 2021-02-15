<?php
namespace OpenDialogAi\AttributeEngine\AttributeValues;

use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;

abstract class AbstractAttributeValue implements AttributeValue
{
    /* @var string $type - one of the valid string types. */
    public static $attributeValueType;

    protected $rawValue;

    public function __construct($rawValue)
    {
        $this->setRawValue($rawValue);
    }

    public function getType(): string
    {
        return static::$attributeValueType;
    }

    public function getRawValue()
    {
        return $this->rawValue;
    }

    public function setRawValue($rawValue)
    {
        $this->rawValue = $rawValue;
    }

    public function jsonSerialize()
    {
        return [
            'type' => static::$attributeValueType,
            'value' => $this->toString()
        ];
    }
}
