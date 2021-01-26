<?php

namespace OpenDialogAi\ContextEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\ContextEngine\ContextManager\BaseContext;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

/**
 * @method static ContextInterface createContext(string $contextId)
 * @method static void addContext(ContextInterface $context)
 * @method static bool hasContext(string $contextId)
 * @method static AttributeInterface getAttribute(string $attributeId, string $contextId)
 * @method static mixed getAttributeValue(string $attributeId, string $contextId, array $index = [])
 * @method static ContextInterface[] getContexts()
 * @method static ContextInterface[] getCustomContexts()
 * @method static BaseContext getSessionContext()
 * @method static UserContext createUserContext(UtteranceInterface $utterance)
 * @method static UserContext getUserContext()
 * @method static BaseContext getConversationContext()
 * @method static void saveAttribute(string $attributeName, $attributeValue)
 * @method static ContextInterface getContext(string $contextId)
 */
class ContextService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ContextEngine\ContextManager\ContextServiceInterface::class;
    }
}
