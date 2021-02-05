<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\BooleanAttributeValue;

/**
 * A BooleanAttribute implementation.
 */
class BooleanAttribute extends BasicScalarAttribute
{
    public static $attributeType = 'attribute.core.boolean';

    /**
     * BooleanAttribute constructor.
     * @param $id
     * @param mixed | null $rawValue
     * @param BooleanAttributeValue|null $value
     */
    public function __construct($id, $rawValue = null, ?BooleanAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new BooleanAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new BooleanAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
