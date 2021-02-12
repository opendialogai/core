<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\SerializedArrayAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * FormData implementation of Attribute.
 *
 * Stores form data as a serialized PHP array.
 *
 * $formData->getTypedValue() retrieves the array itself.
 */
class FormDataAttribute extends BasicScalarAttribute
{
    protected static ?string $componentId = 'attribute.core.formData';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    /**
     * FormAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value = null)
    {
        if ($value instanceof SerializedArrayAttributeValue) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new SerializedArrayAttributeValue($value);
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
