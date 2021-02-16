<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\IntAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicScalarAttribute
{
    protected static string $componentId = 'attribute.core.int';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;
    /**
     * IntAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value = null)
    {
        if ($value instanceof IntAttributeValue) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new IntAttributeValue($value);
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
