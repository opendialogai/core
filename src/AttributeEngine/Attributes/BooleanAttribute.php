<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\BooleanAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * A BooleanAttribute implementation.
 */
class BooleanAttribute extends BasicScalarAttribute
{
    protected static string $componentId = 'attribute.core.boolean';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;


    /**
     * BooleanAttribute constructor.
     * @param $id
     * @param mixed | null $rawValue
     * @param BooleanAttributeValue|null $value
     */
    public function __construct($id, $value = null)
    {
        if ($value instanceof BooleanAttributeValue) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new BooleanAttributeValue($value);
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
