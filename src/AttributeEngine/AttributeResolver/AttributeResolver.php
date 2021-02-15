<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException;

/**
 * The AttributeResolver maps from an attribute identifier to the attribute type for that Attribute.
 * Attribute types extend the @see AbstractAttribute
 */
class AttributeResolver
{
    private Map $supportedAttributes;
    private $attributeTypeService;

    public function __construct()
    {
        $this->supportedAttributes = new Map();
        $this->attributeTypeService = resolve(AttributeTypeServiceInterface::class);
    }

    /**
     * @return Attribute[]|Map
     */
    public function getSupportedAttributes(): Map
    {
        return $this->supportedAttributes;
    }

    /**
     * Registers an array of attributes. The original set of attributes is preserved so this can be run multiple times
     *
     * @param $attributes string[]|Attribute[] Array of attribute class names
     * @throws UnsupportedAttributeTypeException
     */
    public function registerAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $type) {
            if ($this->attributeTypeService->isAttributeTypeClassRegistered($type)) {
                $this->supportedAttributes->put($name, $type);
            } else {
                Log::error(sprintf(
                    "Not registering attribute %s as it has an unknown type %s, please ensure all "
                        . "custom attribute types are registered.",
                    $name,
                    $type
                ));
                throw new UnsupportedAttributeTypeException();
            }
        }
    }

    /**
     * @param string $attributeId
     * @return bool
     */
    public function isAttributeSupported(string $attributeId): bool
    {
        return $this->supportedAttributes->hasKey($attributeId);
    }

    /**
     * Tries to resolve an attribute with the given id to a supported type.
     *
     * @param string $attributeId
     * @param $value
     * @return Attribute
     */
    public function getAttributeFor(string $attributeId, $value = null): Attribute
    {
        if ($this->isAttributeSupported($attributeId)) {
            // First instantiate the attribute so we can see what type it is and construct appropriately.
            $attributeType = $this->supportedAttributes->get($attributeId);
            $attribute = (new $attributeType($attributeId));

            // For scalar attribute we prefer setting the AttributeValue object, but if that is not
            // available we set the raw value. Scalar attributes should always be able to handle null
            // raw values as well.
            if ($attribute instanceof ScalarAttribute) {
                if ($value instanceof AttributeValue) {
                    $attribute->setAttributeValue($value);
                } else {
                    $attribute->setRawValue($value);
                }
            }

            // For composite attributes we expect to either be provided with a prepopulated Ds\Map of
            // attributes or with a single attribute that we add to the composite attribute.
            if ($attribute instanceof CompositeAttribute) {
                if ($value instanceof Map) {
                    $attribute->setAttributes($value);
                } elseif ($value instanceof Attribute) {
                    $attribute->addAttribute($value);
                } elseif (!isset($value)) {
                    return $attribute;
                }
            }
            return $attribute;
        } else {
            Log::debug(sprintf('Attribute %s is not registered, defaulting to String type', $attributeId));
            return new StringAttribute($attributeId, $value);
        }
    }
}
