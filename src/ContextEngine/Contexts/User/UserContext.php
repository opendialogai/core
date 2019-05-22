<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;

class UserContext extends AbstractContext
{
    const USER_CONTEXT = 'user';

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
     * @param ActionResult $actionResult
     * @return ChatbotUser
     */
    public function addActionResult(ActionResult $actionResult): ChatbotUser
    {
        foreach ($actionResult->getResultAttributes()->getAttributes() as $attribute) {
            $this->user->addAttribute($attribute);
        }

        return $this->updateUser();
    }

    /**
     *
     */
    public function updateUser()
    {
        $this->user = $this->userService->updateUser($this->user);
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isUserHavingConversation() : bool
    {
        return $this->userService->userIsHavingConversation($this->user->getId());
    }

    /**
     * @return Conversation
     */
    public function getCurrentConversation(): Conversation
    {
        return $this->userService->getCurrentConversation($this->user->getId());
    }

    /**
     * @param Conversation $conversation
     */
    public function setCurrentConversation(Conversation $conversation)
    {
        $this->user = $this->userService->setCurrentConversation($this->user, $conversation);
    }

    /**
     * @param Intent $intent
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setCurrentIntent(Intent $intent)
    {
        $this->user = $this->userService->setCurrentIntent($this->user, $intent);
    }

    /**
     *
     */
    public function moveCurrentConversationToPast()
    {
        $this->user = $this->userService->moveCurrentConversationToPast($this->user);
    }


    /**
     * @return bool
     */
    public function hasCurrentIntent()
    {
        if ($this->user->hasCurrentIntent()) {
            return true;
        }

        return false;
    }

    /**
     * @return Intent
     */
    public function getCurrentIntent(): ?Intent
    {
        return $this->user->getCurrentIntent();
    }

    /**
     * @return Scene
     */
    public function getCurrentScene(): Scene
    {
        if ($this->user->hasCurrentIntent()) {
            // Get the scene for the current intent
            $sceneId = $this->userService->getSceneForIntent($this->user->getCurrentIntent()->getUid());
            $currentScene = $this->userService->getCurrentConversation($this->user->getId())->getScene($sceneId);
        } else {
            // Set the current intent as the first intent of the opening scene
            /* @var Scene $currentScene */
            $currentScene = $this->user->getCurrentConversation()->getOpeningScenes()->first()->value;
            $this->user->setCurrentIntent($currentScene->getIntentByOrder(1));
            $this->updateUser();
        }

        return $currentScene;
    }

    /**
     * @return bool
     */
    public function currentSpeakerIsBot()
    {
        if ($this->userService->getCurrentSpeaker($this->getCurrentIntent()->getUid()) == Model::BOT) {
            return true;
        }

        return false;
    }
}
