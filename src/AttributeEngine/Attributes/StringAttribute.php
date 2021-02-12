<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * StringAttribute implementation.
 */
class StringAttribute extends BasicScalarAttribute
{
    protected static ?string $componentId = 'attribute.core.string';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    /**
     * StringAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value = null)
    {
        if ($value instanceof StringAttributeValue) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new StringAttributeValue($value);
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
