<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;

class UserContext extends AbstractContext
{
    const USER_CONTEXT = 'user';

    /* @var ChatbotUser */
    private $user;

    /* @var UserService */
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
     * @param string $attributeName
     * @param $value
     * @param null $type
     * @return AttributeInterface
     */
    public function setAttribute(string $attributeName, $value, $type = null): AttributeInterface
    {
        return $this->getUser()->setAttribute($attributeName, $value, $type);
    }

    /**
     * Removes an attribute from the user if there is one with the given ID
     *
     * @param string $attributeName
     * @return bool true if removed, false if not
     */
    public function removeAttribute(string $attributeName): bool
    {
        return $this->getUser()->removeAttribute($attributeName);
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
     * Updates the user in DGraph
     *
     * @return ChatbotUser
     */
    public function updateUser(): ChatbotUser
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
    public function setCurrentConversation(Conversation $conversation): void
    {
        $this->user = $this->userService->setCurrentConversation($this->user, $conversation);
    }

    /**
     * @param Intent $intent
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setCurrentIntent(Intent $intent): void
    {
        $this->user = $this->userService->setCurrentIntent($this->user, $intent);
    }

    /**
     * Moves the user's current conversation to a past conversation
     */
    public function moveCurrentConversationToPast(): void
    {
        $this->user = $this->userService->moveCurrentConversationToPast($this->user);
    }

    /**
     * @return bool
     */
    public function hasCurrentIntent(): bool
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
    public function currentSpeakerIsBot(): bool
    {
        if ($this->userService->getCurrentSpeaker($this->getCurrentIntent()->getUid()) === Model::BOT) {
            return true;
        }

        return false;
    }
}
