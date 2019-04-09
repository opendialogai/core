<?php


namespace OpenDialogAi\ContextEngine\Contexts;


use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\ChatbotUser;

class UserContext extends AbstractContext
{
    const USER_CONTEXT = 'context.core.user';

    /* @var \OpenDialogAi\Core\Conversation\ChatbotUser */
    private $user;

    /* @var \OpenDialogAi\ContextEngine\Contexts\User\UserService */
    private $userService;

    public function __construct(ChatbotUser $user, UserService $userService)
    {
        parent::__construct(self::USER_CONTEXT);
        $this->user = $user;
        $this->userService = $userService;

        // Move all the user attributes to the context;
        $this->setAttributes($this->user->getAttributes());
    }

    public function getUser(): ChatbotUser
    {
        return $this->user;
    }

    public function getUserId(): string
    {
        return $this->user->getAttribute('id')->getValue();
    }

    public function updateUser()
    {
        $this->userService->updateUser($this->user);
    }
}
