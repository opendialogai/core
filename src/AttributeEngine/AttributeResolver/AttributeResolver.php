<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
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
     * Registers an array of attributes. The original set of attributes is preserved so this can be run multiple times
     *
     * @param $attributes string[]|AttributeInterface[] Array of attribute class names
     * @throws UnsupportedAttributeTypeException
     */
    public function registerAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $type) {
            if ($this->attributeTypeService->isAttributeTypeClassRegistered($type)) {
                $this->supportedAttributes[$name] = $type;
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
     * @param string $attributeId
     * @param $value
     * @return Attribute
     */
    public function getAttributeFor(string $attributeId, $rawValue = null, AttributeValue $value = null)
    {
        if ($this->isAttributeSupported($attributeId)) {
            // First instantiate the attribute so we can see what type it is and construct appropriately.
            $attribute = new $this->supportedAttributes[$attributeId]($attributeId);

            // For scalar attribute we prefer setting the AttributeValue object, but if that is not
            // available we set the raw value. Scalar attributes should always be able to handle null
            // raw values as well.
            if ($attribute instanceof ScalarAttribute) {
                is_null($value) ? $attribute->setRawValue($rawValue) : $attribute->setAttributeValue($value);
            }

            // For composite attributes we expect to either be provided with a prepopulated Ds\Map of
            // attributes or with a single attribute that we add to the composite attribute.
            if ($attribute instanceof CompositeAttribute) {
                if ($rawValue instanceof Map) {
                    $attribute->setAttributes($rawValue);
                } elseif ($rawValue instanceof Attribute) {
                    $attribute->addAttribute($rawValue);
                }
            }
            return $attribute;
        } else {
            Log::debug(sprintf('Attribute %s is not registered, defaulting to String type', $attributeId));
            return new StringAttribute($attributeId, $rawValue);
        }
    }
}
