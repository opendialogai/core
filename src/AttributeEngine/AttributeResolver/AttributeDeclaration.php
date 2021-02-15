<?php


namespace OpenDialogAi\AttributeEngine\AttributeResolver;


class AttributeDeclaration
{
    private string $attributeTypeClass;

    /**
     * AttributeDeclaration constructor.
     * @param string $name
     * @param Attribute|string $attributeTypeClass
     */
    public function __construct($name, $attributeTypeClass)
    {
        $this->attributeTypeClass = $attributeTypeClass;
    }

    /**
     * @return string
     */
    public function getAttributeTypeClass(): string
    {
        return $this->attributeTypeClass;
    }
}
