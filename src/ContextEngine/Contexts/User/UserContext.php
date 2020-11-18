<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\UserAttribute;

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
     * Returns all the attributes currently associated with this context.
     *
     * @return Map
     */
    public function getAttributes(): Map
    {
        return $this->userService->getUserAttributes($this->getUser());
    }

    /**
     * @param string $attributeName
     * @return AttributeInterface
     * @throws AttributeDoesNotExistException
     */
    public function getAttribute(string $attributeName): AttributeInterface
    {
        if ($this->userService->hasUserAttribute($this->getUser(), $attributeName)) {
            /** @var UserAttribute $userAttribute */
            $userAttribute = $this->userService->getUserAttributes($this->getUser())->get($attributeName);
            return $userAttribute->getInternalAttribute();
        } else {
            Log::warning(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
            throw new AttributeDoesNotExistException(
                sprintf("Cannot return attribute with name %s - does not exist", $attributeName)
            );
        }
    }

    /**
     * @param string $attributeName
     * @return mixed
     */
    public function getAttributeValue(string $attributeName)
    {
        if ($this->userService->hasUserAttribute($this->getUser(), $attributeName)) {
            $userAttribute = $this->userService->getUserAttributes($this->getUser())->get($attributeName);
            return $userAttribute->getInternalAttribute()->getValue();
        }

        Log::debug(sprintf('Trying get value of a user attribute that does not exist - %s', $attributeName));
        return null;
    }

    /**
     * @inheritDoc
     */
    public function addAttribute(AttributeInterface $attribute): UserContext
    {
        $this->userService->addUserAttribute($this->getUser(), $attribute);
        return $this;
    }

    /**
     * Removes an attribute from the user if there is one with the given ID
     *
     * @param string $attributeName
     * @return bool true if removed, false if not
     */
    public function removeAttribute(string $attributeName): bool
    {
        if ($this->userService->hasUserAttribute($this->getUser(), $attributeName)) {
            /** @var UserAttribute $userAttribute */
            $userAttribute = $this->userService->getUserAttributes($this->getUser())->get($attributeName);
            $userAttribute->setValue(null);
            return true;
        }

        Log::warning(sprintf(
            'Trying to remove non-existent attribute %s from %s',
            $attributeName,
            $this->getId()
        ));

        return false;
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
    public function isUserHavingConversation(): bool
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
     * @param Conversation $conversationForCloning Required to ensure that the new conversation is fully
     * cloned by `UserService.updateUser`
     * @param Conversation $conversationForConnecting Required to ensure that DGraph contains a correct `instance_of`
     * edge between template & instance
     * @return string
     */
    public function setCurrentConversation(Conversation $conversationForCloning, Conversation $conversationForConnecting): string
    {
        $this->user = $this->userService->setCurrentConversation(
            $this->user,
            $conversationForCloning,
            $conversationForConnecting
        );

        return $this->user->getCurrentConversationUid();
    }

    /**
     * Gets just the current intent unconnected
     *
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getCurrentIntent(): EIModelIntent
    {
        $currentIntentId = $this->user->getCurrentIntentUid();
        return $this->conversationStore->getEIModelIntentByUid($currentIntentId);
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
     * @throws CurrentIntentNotSetException
     */
    public function getCurrentScene(): Scene
    {
        if (!$this->user->hasCurrentIntent()) {
            throw new CurrentIntentNotSetException("Attempted to get the current scene without having set a current intent.");
        }

        $currentIntent = $this->conversationStore->getEIModelIntentByUid($this->user->getCurrentIntentUid());

        // Get the scene for the current intent
        $sceneId = $this->userService->getSceneForIntent($currentIntent->getIntentUid());

        // use the conversation that is against the user
        $currentScene = $this->userService->getCurrentConversation($this->user->getId())->getScene($sceneId);

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
