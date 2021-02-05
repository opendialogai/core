<?php


namespace OpenDialogAi\AttributeEngine\Attributes;

class BasicCompositeAttribute extends AbstractCompositeAttribute
{
    public static $attributeType = 'attribute.core.composite';

    public function toString(): ?string
    {
        return "CompositeAttribute";
    }
}
