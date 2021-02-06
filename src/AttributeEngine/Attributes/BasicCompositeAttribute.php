<?php


namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\Core\Components\ODComponentTypes;

class BasicCompositeAttribute extends AbstractCompositeAttribute
{
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;
    protected static ?string $componentId = 'attribute.core.composite';

    public function toString(): ?string
    {
        return "CompositeAttribute";
    }
}
