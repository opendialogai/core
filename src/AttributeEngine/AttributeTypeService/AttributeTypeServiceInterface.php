<?php


namespace OpenDialogAi\AttributeEngine\AttributeTypeService;


use Ds\Map;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeAlreadyRegisteredException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeInvalidException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;

interface AttributeTypeServiceInterface
{
    /**
     * Returns a mapping of available attributes types indexed by their attribute type ID, eg.
     *  [
     *      'attribute.core.string' => StringAttribute::class,
     *      'attribute.core.int' => IntAttribute::class,
     *      ...
     *  ]
     *
     * @return Map
     */
    public function getAvailableAttributeTypes(): Map;

    /**
     * Returns whether an attribute type with the given ID has been registered.
     *
     * @param string $attributeTypeId
     * @return bool
     */
    public function isAttributeTypeAvailable(string $attributeTypeId): bool;

    /**
     * Returns an attribute type class for the given ID.
     *
     * @param string $attributeTypeId
     * @return string
     * @throws AttributeTypeNotRegisteredException
     */
    public function getAttributeTypeClass(string $attributeTypeId): string;

    /**
     * Registers the given attribute class so that it is available via this service.
     *
     * @param string $attributeTypeClass
     * @throws AttributeTypeAlreadyRegisteredException
     * @throws AttributeTypeInvalidException
     */
    public function registerAttributeType(string $attributeTypeClass): void;
}
