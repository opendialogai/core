<?php


namespace OpenDialogAi\AttributeEngine\AttributeResolver;


use OpenDialogAi\AttributeEngine\Contracts\Attribute;

class AttributeDeclaration
{
    private string $attributeId;
    private string $attributeTypeClass;
    private string $source;

    /**
     * AttributeDeclaration constructor.
     * @param string $attributeId
     * @param Attribute|string $attributeTypeClass
     * @param string $source
     */
    public function __construct(string $attributeId, $attributeTypeClass, string $source)
    {
        $this->attributeId = $attributeId;
        $this->attributeTypeClass = $attributeTypeClass;
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getAttributeId(): string
    {
        return $this->attributeId;
    }

    /**
     * @return string
     */
    public function getAttributeTypeClass(): string
    {
        return $this->attributeTypeClass;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
