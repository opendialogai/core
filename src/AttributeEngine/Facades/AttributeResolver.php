<?php

namespace OpenDialogAi\AttributeEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;

/**
 * @method static Attribute getAttributeFor(string $attributeId, $value)
 * @method static Attribute[] getSupportedAttributes()
 * @method static void registerAttributes($attributes)
 * @method static string getValidIdPattern()
 * @method static string getValidTypePattern()
 * @method static bool isAttributeSupported(string $attributeId)
 * @method static bool isValidId(string $id)
 */
class AttributeResolver extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver::class;
    }
}
