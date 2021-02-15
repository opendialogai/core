<?php

namespace OpenDialogAi\ContextEngine\Contexts\BaseContexts;

use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\ContextService\CoreContextService;

class ConversationContext extends AbstractContext
{
    protected static ?string $componentId = CoreContextService::CONVERSATION_CONTEXT;
}
