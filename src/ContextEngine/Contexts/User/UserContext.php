<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;
use OpenDialogAi\ContextEngine\Contexts\BaseScopedContext;
use OpenDialogAi\ContextEngine\Contexts\PersistentContext;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\ScopeNotSetException;
use OpenDialogAi\ContextEngine\Exceptions\UserContextMissingIncomingUserInfo;

class UserContext extends PersistentContext
{
    public const USER_CONTEXT = 'user';
    public const UTTERANCE_USER = 'utterance_user';

    protected $persistentAttributes = [
        UserAttribute::CURRENT_USER
    ];

    public function __construct(ContextDataClient $dataClient)
    {
        parent::__construct(self::USER_CONTEXT, $dataClient);
    }
}
