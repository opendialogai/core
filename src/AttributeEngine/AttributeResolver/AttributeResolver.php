<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AbstractAttribute;
use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\AttributeEngine\StringAttribute;

/**
 * The AttributeResolver maps from an attribute identifier to the attribute type for that Attribute.
 * Attribute types extend the @see AbstractAttribute
 */
class AttributeResolver
{
    /* @var array */
    private $supportedAttributes = [];

    public function __construct()
    {
        $this->supportedAttributes = $this->getSupportedAttributes();
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
     * @param $attributes AttributeInterface[]
     */
    public function registerAttributes($attributes): void
    {
        foreach ($attributes as $name => $type) {
            if (class_exists($type) && in_array(AttributeInterface::class, class_implements($type))) {
                $this->supportedAttributes[$name] = $type;
            } else {
                Log::error(sprintf("Not registering attribute %s - has unknown type %s", $name, $type));
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
}
