<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\Core\Components\ODComponentTypes;

class BasicScalarAttribute extends AbstractScalarAttribute
{
    protected static ?string $componentId = 'attribute.core.basic_scalar';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;


    public function setRawValue($rawValue)
    {
        is_null($this->value) ?
            $this->setAttributeValue(new StringAttributeValue($rawValue)) : $this->value->setRawValue($rawValue);
    }

    public function toString(): ?string
    {
        return (string) $this->getValue()->getRawValue();
    }
}
