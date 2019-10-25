<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
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

    /** @var ConversationStoreInterface */
    private $conversationStore;

    public function __construct(
        ChatbotUser $user,
        UserService $userService,
        ConversationStoreInterface $conversationStore
    ) {
        parent::__construct(self::USER_CONTEXT);
        $this->user = $user;
        $this->userService = $userService;
        $this->conversationStore = $conversationStore;
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
        return $this->user->isHavingConversation();
    }

    /**
     * @return Conversation
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     * @throws EIModelCreatorException
     */
    public function getCurrentConversation(): Conversation
    {
        return $this->userService->getCurrentConversation($this->user->getId());
    }

    /**
     * Sets the current conversation against the user, persists the user and returns the conversation id
     *
     * @param Conversation $conversation
     * @return string
     */
    public function setCurrentConversation(Conversation $conversation): string
    {
        $this->user = $this->userService->setCurrentConversation($this->user, $conversation);
        return $this->user->getCurrentConversationUid();
    }

    /**
     * Gets just the current intent unconnected
     *
     * @return Intent
     * @throws EIModelCreatorException
     */
    public function getCurrentIntent(): Intent
    {
        $currentIntentId = $this->user->getCurrentIntentUid();
        return $this->conversationStore->getIntentByUid($currentIntentId);
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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        return $this->user->hasCurrentIntent();
    }

    /**
     * @return Scene
     * @throws EIModelCreatorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function getCurrentScene(): Scene
    {
        if ($this->user->hasCurrentIntent()) {
            $currentIntent = $this->conversationStore->getEIModelIntentByUid($this->user->getCurrentIntentUid());

            // Get the scene for the current intent
            $sceneId = $this->userService->getSceneForIntent($currentIntent->getIntentUid());

            // use the conversation that is against the user
            $currentScene = $this->userService->getCurrentConversation($this->user->getId())->getScene($sceneId);
        } else {
            // Set the current intent as the first intent of the opening scene
            /* @var Scene $currentScene */
            $currentScene = $this->user->getCurrentConversation()->getOpeningScenes()->first()->value;

            $intent = $currentScene->getIntentByOrder(1);
            $this->setCurrentIntent($intent);
        }

        return $currentScene;
    }

    /**
     * @return bool
     */
    public function currentSpeakerIsBot(): bool
    {
        if ($this->userService->getCurrentSpeaker($this->user->getCurrentIntentUid()) === Model::BOT) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function persist(): bool
    {
        $this->updateUser();
        return true;
    }
}
