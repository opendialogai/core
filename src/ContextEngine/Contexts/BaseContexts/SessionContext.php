<?php

namespace OpenDialogAi\ContextEngine\Contexts\BaseContexts;

use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\ContextService\CoreContextService;

class SessionContext extends AbstractContext
{
    protected static ?string $componentId = CoreContextService::SESSION_CONTEXT;
}
