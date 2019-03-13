<?php


namespace OpenDialogAi\Core\Attribute;

use Ds\Map;

trait HasAttributesTrait
{

    /* @var Map $attributes - the set of attributes related to this node - they key to the attribute defines the
     * relationship
     */
    protected $attributes;

    /**
     * @return Map
     */
    public function getAttributes()
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

    public function getAttribute($attributeName)
    {
        return $this->attributes->get($attributeName);
    }
}
