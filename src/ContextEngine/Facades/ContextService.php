<?php

namespace OpenDialogAi\ContextEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contracts\Context;

/**
 * @method static Context createContext(string $contextId)
 * @method static void addContext(Context $context)
 * @method static bool hasContext(string $contextId)
 * @method static Attribute getAttribute(string $attributeId, string $contextId)
 * @method static mixed getAttributeValue(string $attributeId, string $contextId)
 * @method static Context[] getContexts()
 * @method static Context[] getCustomContexts()
 * @method static void saveAttribute(string $attributeName, $attributeValue)
 * @method static Context getContext(string $contextId)
 * @method static Context getSessionContext()
 * @method static void loadPersistentContextAttributes(string $userId)
 */
class ContextService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ContextEngine\Contracts\ContextService::class;
    }
}
