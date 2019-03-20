<?php

namespace OpenDialogAi\AttributeEngine\AttributeResolver;

use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\BasicAttribute;
use OpenDialogAi\Core\Attribute\AttributeInterface;

class AttributeResolverService
{
    const ATTRIBUTE_RESOLVER = 'attribute_resolver';

    public function __construct()
    {
        //@todo
    }

    public function getAvailableAttributes()
    {
        return config('opendialog.attribute_engine.available_attributes');
    }

    public function isAttributeSupported(string $attributeId)
    {
        //@todo
    }

    /**
     * @param string $attributeId
     * @return AttributeInterface
     */
    public function getAttributeFor(string $attributeId)
    {
        /* @var AttributeInterface $attribute */
        $attribute = new BasicAttribute($attributeId, AbstractAttribute::STRING, 'dummy');
        return $attribute;
    }
}
