<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;
use OpenDialogAi\ContextEngine\Contexts\BaseScopedContext;
use OpenDialogAi\ContextEngine\Contexts\PersistentContext;
use OpenDialogAi\ContextEngine\DataClients\GraphAttributeDataClient;

class UserContext extends PersistentContext
{
    protected static string $componentId = self::USER_CONTEXT;

    protected static ?string $componentName = 'User';
    protected static ?string $componentDescription = 'A context for storing data about the user.';

    public const USER_CONTEXT = 'user';
    public const UTTERANCE_USER = 'utterance_user';

    public function __construct(GraphAttributeDataClient $dataClient)
    {
        parent::__construct($dataClient);
    }
}
