<?php


namespace OpenDialogAi\Core\Attribute;

use Ds\Map;
use Illuminate\Support\Facades\Log;


/**
 * A trait that anything that needs to deal with Attributes can use.
 *
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
     * @inheritdoc
     */
    public function addAttribute(AttributeInterface $attribute): void
    {
        $this->attributes->put($attribute->getId(), $attribute);
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
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute(string $attributeName) : AttributeInterface
    {
        if ($this->hasAttribute($attributeName)) {
            Log::debug(sprintf("Returning attribute with name %", $attributeName));
            return $this->attributes->get($attributeName);
        }

        Log::debug(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
        throw new AttributeDoesNotExistException();
    }
}
