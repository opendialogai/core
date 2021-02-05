<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\SerializedArrayAttributeValue;

/**
 * FormData implementation of Attribute.
 *
 * Stores form data as a serialized PHP array.
 *
 * $formData->getTypedValue() retrieves the array itself.
 */
class FormDataAttribute extends BasicScalarAttribute
{
    public static $attributeType = 'attribute.core.formData';

    /**
     * FloatAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $rawValue = null, ?SerializedArrayAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new SerializedArrayAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new SerializedArrayAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
