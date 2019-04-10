<?php


namespace OpenDialogAi\ContextEngine\Contexts;


use Ds\Map;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;

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
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): Map
    {
        return $this->getUser()->getAttributes();
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attributeName): AttributeInterface
    {
        return $this->getUser()->getAttribute($attributeName);
    }

    /**
     * @inheritDoc
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->getUser()->addAttribute($attribute);
    }

    /**
     * @return ChatbotUser
     */
    public function getUser(): ChatbotUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user->getId();
    }

    /**
     *
     */
    public function updateUser()
    {
        $this->userService->updateUser($this->user);
    }

    /**
     * @return bool
     */
    public function isUserHavingConversation() : bool
    {
        return $this->userService->userIsHavingConversation($this->user->getId());
    }

    public function getCurrentConversation(): Conversation
    {
        return $this->userService->getCurrentConversation($this->user->getId());
    }

    public function setCurrentConversation(Conversation $conversation)
    {
        $this->getUser()->setCurrentConversation($conversation);
        $this->updateUser();
    }
}
