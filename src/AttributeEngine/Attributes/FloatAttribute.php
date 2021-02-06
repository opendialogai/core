<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\FloatAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends BasicScalarAttribute
{
    protected static ?string $componentId = 'attribute.core.float';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    /**
     * FloatAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $rawValue = null, ?FloatAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new FloatAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new FloatAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
