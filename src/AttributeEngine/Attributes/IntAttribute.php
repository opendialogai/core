<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\IntAttributeValue;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicScalarAttribute
{
    public static $attributeType = 'attribute.core.int';

    /**
     * IntAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $rawValue = null, ?IntAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new IntAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new IntAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
