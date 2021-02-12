<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\BaseScopedContext;
use OpenDialogAi\ContextEngine\Exceptions\ScopeNotSetException;
use OpenDialogAi\ContextEngine\Exceptions\UserContextMissingIncomingUserInfo;

class UserContext extends BaseScopedContext
{
    public const USER_CONTEXT = 'user';
    public const CURRENT_USER = 'current_user';
    public const UTTERANCE_USER = 'utterance_user';
    public const USER_ID_SCOPE = 'user_id';

    protected UserDataClient $dataClient;

    public function __construct(UserDataClient $dataClient)
    {
        parent::__construct(self::USER_CONTEXT);
        $this->dataClient = $dataClient;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute(string $attributeName): Attribute
    {
        if ($attributeName == self::CURRENT_USER) {
            return $this->getUser();
        }

        return parent::getAttribute($attributeName);
    }

    public function getUser(): Attribute
    {
        if (!array_key_exists(self::USER_ID_SCOPE, $this->getScope())) {
            throw new ScopeNotSetException('Cannot retrieve user from user context as user scope is not set');
        }

        if (!$this->hasAttribute(self::UTTERANCE_USER)) {
            throw new UserContextMissingIncomingUserInfo('User context does not have any incoming user information');
        }

        $user = $this->dataClient->createOrUpdate(
            $this->getScope()[self::USER_ID_SCOPE], $this->getAttribute(self::UTTERANCE_USER)
        );

        return $user;
    }
}
