<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;

class AttributeEngineReflection implements AttributeEngineReflectionInterface
{
    /**
     * @var AttributeResolver
     */
    private $attributeResolver;

    /**
     * @var AttributeTypeServiceInterface
     */
    private $attributeTypeService;

    /**
     * AttributeEngineReflection constructor.
     * @param AttributeResolver $attributeResolver
     * @param AttributeTypeServiceInterface $attributeTypeService
     */
    public function __construct(AttributeResolver $attributeResolver, AttributeTypeServiceInterface $attributeTypeService)
    {
        $this->attributeResolver = $attributeResolver;
        $this->attributeTypeService = $attributeTypeService;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableAttributes(): Map
    {
        return new Map($this->attributeResolver->getSupportedAttributes());
    }

    /**
     * @inheritDoc
     */
    public function getAvailableAttributeTypes(): Map
    {
        return $this->attributeTypeService->getAvailableAttributeTypes();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_attributes" => $this->getAvailableAttributes()->toArray(),
            "available_attribute_types" => $this->getAvailableAttributeTypes()->toArray(),
        ];
    }
}
