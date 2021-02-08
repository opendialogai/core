<?php

namespace OpenDialogAi\AttributeEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;

/**
 * @method static AttributeInterface getAttributeFor(string $attributeId, mixed $value)
 * @method static AttributeInterface[] getSupportedAttributes()
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
