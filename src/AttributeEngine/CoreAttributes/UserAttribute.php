<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;

class UserAttribute extends BasicCompositeAttribute
{
    public static $attributeType = 'attribute.core.user';

    public function setUserAttribute(string $type, $value)
    {
        $utteranceType = AttributeResolver::getAttributeFor($type, $value);
        $this->addAttribute($utteranceType);
        return $this;
    }
}
