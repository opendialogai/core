<?php


namespace OpenDialogAi\AttributeEngine\AttributeTypeService;


use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeAlreadyRegisteredException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeInvalidException;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;

class AttributeTypeService implements AttributeTypeServiceInterface
{
    /**
     * @var Map
     */
    private $attributeTypes;

    /**
     * AttributeTypeService constructor.
     */
    public function __construct()
    {
        $this->attributeTypes = new Map();
    }

    /**
     * @param string|AttributeInterface $attributeType
     * @return bool
     */
    public function isValidAttributeType(string $attributeType): bool
    {
        return class_exists($attributeType)
            && in_array(AttributeInterface::class, class_implements($attributeType));
    }


    /**
     * @inheritDoc
     */
    public function getAvailableAttributeTypes(): Map
    {
        return $this->attributeTypes;
    }

    /**
     * @inheritDoc
     */
    public function isAttributeTypeAvailable(string $attributeTypeId): bool
    {
        return $this->attributeTypes->hasKey($attributeTypeId);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeTypeClassRegistered(string $attributeType): bool
    {
        return $this->attributeTypes->hasValue($attributeType);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeTypeClass(string $attributeTypeId): string
    {
        if ($this->isAttributeTypeAvailable($attributeTypeId)) {
            return $this->attributeTypes->get($attributeTypeId);
        } else {
            throw new AttributeTypeNotRegisteredException();
        }
    }

    /**
     * @inheritDoc
     */
    public function registerAttributeType(string $attributeType): void
    {
        if ($this->isValidAttributeType($attributeType)) {
            if ($this->isAttributeTypeAvailable($attributeType::getType())) {
                throw new AttributeTypeAlreadyRegisteredException();
            } else {
                $this->attributeTypes->put($attributeType::getType(), $attributeType);
            }
        } else {
            throw new AttributeTypeInvalidException();
        }
    }

    /**
     * @inheritDoc
     */
    public function registerAttributeTypes(array $attributeTypes): void
    {
        foreach ($attributeTypes as $attributeType) {
            try {
                $this->registerAttributeType($attributeType);
            } catch (AttributeTypeAlreadyRegisteredException $e) {
                Log::warning(sprintf(
                    'Not registering attribute type \'%s\', an attribute type with the ID \'%s\' is already registered.',
                    $attributeType,
                    $attributeType::getType()
                ));
            } catch (AttributeTypeInvalidException $e) {
                Log::warning(sprintf(
                    'Not registering attribute type \'%s\', the attribute type was invalid.',
                    $attributeType
                ));
            }
        }
    }
}
