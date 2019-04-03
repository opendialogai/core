<?php


namespace OpenDialogAi\ContextEngine\Contexts;


use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;

class UserContext extends AbstractContext
{
    const USER_CONTEXT = 'context.core.user';

    public function __construct()
    {
        parent::__construct(self::USER_CONTEXT);
    }
}
