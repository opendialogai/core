<?php


namespace OpenDialogAi\Core\Attribute;

use Ds\Map;

/**
 * A trait that anything that needs to deal with Attributes can use.
 *
 */
trait HasAttributesTrait
{

    /**
     * @var Map $attributes - the set of attributes related to this node - they key to the attribute defines the
     * relationship
     */
    protected $attributes;

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

    public function addAttribute(AttributeInterface $attribute)
    {
        $this->attributes->put($attribute->getId(), $attribute);
    }

    public function getAttribute(string $attributeName): AttributeInterface
    {
        return $this->attributes->get($attributeName);
    }
}
