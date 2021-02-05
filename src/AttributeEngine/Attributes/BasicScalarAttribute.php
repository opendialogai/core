<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;

class BasicScalarAttribute extends AbstractScalarAttribute
{
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
