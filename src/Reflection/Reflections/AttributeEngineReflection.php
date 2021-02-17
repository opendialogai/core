<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeDeclaration;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\Core\Components\Contracts\OpenDialogComponentData;
use OpenDialogAi\Core\Components\ODComponentTypes;

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
        return $this->attributeResolver->getSupportedAttributes();
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
        $attributeTypes = $this->getAvailableAttributeTypes();
        $attributes = $this->getAvailableAttributes();

        $attributeTypesWithData = array_map(function ($attributeType) {
            /** @var AttributeInterface $attributeType */
            return [
                'component_data' => (array) $attributeType::getComponentData(),
            ];
        }, $attributeTypes->toArray());

        $attributesWithData = array_map(function ($attributeDeclaration) {
            /** @var AttributeDeclaration $attributeDeclaration */

            /** @var AttributeInterface $attributeTypeClass */
            $attributeTypeClass = $attributeDeclaration->getAttributeTypeClass();
            return [
                'component_data' => (array) new OpenDialogComponentData(
                    ODComponentTypes::ATTRIBUTE_COMPONENT_TYPE,
                    $attributeDeclaration->getSource(),
                    $attributeDeclaration->getAttributeId()
                ),
                'attribute_data' => [
                    'type' => $attributeTypeClass::getComponentId(),
                ]
            ];
        }, $attributes->toArray());

        return [
            "available_attributes" => $attributesWithData,
            "available_attribute_types" => $attributeTypesWithData,
        ];
    }
}
