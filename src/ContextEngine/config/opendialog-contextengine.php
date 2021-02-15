<?php

use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\MessageHistory\MessageHistoryContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;

return [
    'supported_contexts' => [
        SessionContext::class,
        ConversationContext::class,
        UserContext::class,
        IntentContext::class,
        MessageHistoryContext::class,
    ]
];
