<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\DynamicAttribute;

/**
 * The AttributeResolver maps from an attribute identifier to the attribute type for that Attribute.
 * Attribute types extend the @see AbstractAttribute
 */
class AttributeResolver
{
    public static $validIdPattern = "/^([a-z]+_)*[a-z]+$/";
    public static $validTypePattern = "/^attribute\.[A-Za-z]+\.[A-Za-z_]+$/";
    /* @var array */
    private $supportedAttributes = [];
    private $attributeTypeService;

    public function __construct()
    {
        $this->supportedAttributes = $this->getSupportedAttributes();
        $this->attributeTypeService = resolve(AttributeTypeServiceInterface::class);
    }

    /**
     * @return AttributeInterface[]
     */
    public function getSupportedAttributes()
    {
        return $this->supportedAttributes;
    }

    /**
     * Checks if the id of the DynamicAttribute is in the right format
     *
     * @param  string  $id
     *
     * @return bool
     */
    public static function isValidId(string $id): bool
    {
        return preg_match(AttributeResolver::$validIdPattern, $id) === 1;
    }

    /**
     * Checks if the type of the DynamicAttribute is in the right format
     *
     * @param  string  $type
     *
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return preg_match(AttributeResolver::$validTypePattern, $type) === 1;
    }

    /**
     * Registers an array of attributes. The original set of attributes is preserved so this can be run multiple times
     *
     * @param $attributes string[]|AttributeInterface[] Array of attribute class names
     */
    public function registerAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $type) {
            if ($this->attributeTypeService->isAttributeTypeClassRegistered($type)) {
                $this->supportedAttributes[$name] = $type;
            } else {
                Log::error(sprintf("Not registering attribute %s - has unknown type %s", $name, $type));
            }
        }
    }


    public function registerAllDynamicAttributes(): void
    {
        foreach (DynamicAttribute::all() as $dynamicAttribute) {
            if ($this->isAttributeSupported($dynamicAttribute->attribute_id)) {
                Log::error(sprintf("Not registering dynamic attribute %s (database id: %d)
                     - the attribute name is already in use.", $dynamicAttribute->attribute_id, $dynamicAttribute->id));
                continue;
            }
            if ($this->attributeTypeService->isAttributeTypeAvailable($dynamicAttribute->attribute_type)) {
                $attributeTypeClass = $this->attributeTypeService->getAttributeTypeClass($dynamicAttribute->attribute_type);
                $this->supportedAttributes[$dynamicAttribute->attribute_id] = $attributeTypeClass;
            } else {
                Log::error(sprintf("Not registering dynamic attribute %s - has unknown attribute type identifier %s",
                    $dynamicAttribute->attribute_id, $dynamicAttribute->attribute_type));
            }
        }

    }

    /**
     * @param  string  $attributeId
     *
     * @return bool
     */
    public function isAttributeSupported(string $attributeId)
    {
        if (isset($this->supportedAttributes[$attributeId])) {
            return true;
        }

        return false;
    }

    /**
     * Tries to resolve an attribute with the given id to a supported type.
     *
     * @param  string  $attributeId
     * @param          $value
     *
     * @return AttributeInterface
     */
    public function getAttributeFor(string $attributeId, $value)
    {
        if ($this->isAttributeSupported($attributeId)) {
            return new $this->supportedAttributes[$attributeId]($attributeId, $value);
        } else {
            Log::debug(sprintf('Attribute %s is not registered, defaulting to String type', $attributeId));
            return new StringAttribute($attributeId, $value);
        }
    }


    /**
     * @return string
     */
    public function getValidIdPattern(): string
    {
        return static::$validIdPattern;
    }

    /**
     * @return string
     */
    public function getValidTypePattern(): string
    {
        return static::$validTypePattern;
    }
}
