<?php

namespace OpenDialogAi\Core\Attribute;

use Ds\Map;
use Illuminate\Support\Facades\Log;

/**
 * A trait that anything that needs to deal with Attributes can use.
 */
trait HasAttributesTrait
{
    /**
     * @var Map $attributes - the set of attributes related to this object.
     */
    protected $attributes;

    /**
     * @inheritdoc
     */
    public function hasAttribute($attributeName): bool
    {
        return $this->attributes->hasKey($attributeName);
    }

    /**
     * @inheritdoc
     */
    public function hasAllAttributes($attributes): bool
    {
        foreach ($attributes as $attribute) {
            if (!$this->attributes->hasKey($attribute)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param AttributeInterface $attribute
     * @return $this
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->attributes->put($attribute->getId(), $attribute);
        return $this;
    }

    /**
     * @return Map
     */
    public function getAttributes(): Map
    {
        return $this->attributes;
    }

    /**
     * @param Map $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $attributeName
     * @param $value
     * @return AttributeInterface
     */
    public function setAttribute(string $attributeName, $value): AttributeInterface
    {
        // If the attribute exists update its value
        if ($this->hasAttribute($attributeName)) {
            $attribute = $this->getAttribute($attributeName);
            $attribute->setValue($value);
            return $this->getAttribute($attributeName);
        }

        throw new AttributeDoesNotExistException(
            sprintf('Tried to set %s attribute value that does not exist.', $attributeName)
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttribute(string $attributeName) : AttributeInterface
    {
        if ($this->hasAttribute($attributeName)) {
            return $this->attributes->get($attributeName);
        }

        Log::debug(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
        throw new AttributeDoesNotExistException(
            sprintf("Cannot return attribute with name %s - does not exist", $attributeName)
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributeValue(string $attributeName)
    {
        if ($this->hasAttribute($attributeName)) {
            return $this->getAttribute($attributeName)->getValue();
        }

        Log::debug(sprintf('Trying get value of an attribute that does not exist - %s', $attributeName));
        return null;
    }

    /**
     * Rather than removing the attribute, we set the value to null
     *
     * @param string $attributeName
     * @return bool
     */
    public function removeAttribute(string $attributeName): bool
    {
        if ($this->hasAttribute($attributeName)) {
            $this->getAttribute($attributeName)->setValue(null);
            return true;
        }

        Log::warning(sprintf(
            'Trying to remove non-existent attribute %s from %s',
            $attributeName,
            $this->getId()
        ));
        return false;
    }
}
