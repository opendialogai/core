<?php

namespace OpenDialogAi\AttributeEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\AttributeInterface;

/**
 * @method static AttributeInterface getAttributeFor(string $attributeId, mixed $value)
 * @method static AttributeInterface[] getSupportedAttributes()
 * @method static void registerAttributes($attributes)
 */
class AttributeResolver extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver::class;
    }
}
