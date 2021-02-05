<?php

namespace OpenDialogAi\AttributeEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;

/**
 * @method static Attribute getAttributeFor(string $attributeId, $rawValue = null, AttributeValue $value = null)
 * @method static Attribute[] getSupportedAttributes()
 * @method static void registerAttributes($attributes)
 */
class AttributeResolver extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver::class;
    }
}
