<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\TimestampAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * A TimestampAttribute implementation.
 */
class TimestampAttribute extends AbstractScalarAttribute
{
    protected static ?string $componentId = 'attribute.core.timestamp';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public function __construct($id, $rawValue = null, ?TimestampAttributeValue $value = null)
    {
        if (!is_null($value)) {
            parent::__construct($id, $value);
        } else {
            $attributeValue = new TimestampAttributeValue($rawValue);
            parent::__construct($id, $attributeValue);
        }
    }

    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new TimestampAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return $this->getAttributeValue()->toString();
    }
}
