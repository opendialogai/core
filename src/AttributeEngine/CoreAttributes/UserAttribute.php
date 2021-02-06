<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;

class UserAttribute extends BasicCompositeAttribute
{
    protected static ?string $componentId = 'attribute.core.user';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public function setUserAttribute(string $type, $value)
    {
        $utteranceType = AttributeResolver::getAttributeFor($type, $value);
        $this->addAttribute($utteranceType);
        return $this;
    }
}
