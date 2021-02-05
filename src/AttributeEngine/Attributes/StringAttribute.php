<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;

/**
 * StringAttribute implementation.
 */
class StringAttribute extends BasicScalarAttribute
{
    public static $attributeType = 'attribute.core.string';

    /**
     * StringAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $rawValue = null, ?StringAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new StringAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new StringAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
