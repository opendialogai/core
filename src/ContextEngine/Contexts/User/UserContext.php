<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
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

class UserContext implements ContextInterface
{
    const USER_CONTEXT = 'user';

    private $id;

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
        $this->setId(self::USER_CONTEXT);

        $this->user = $user;
        $this->userService = $userService;
        $this->conversationStore = $conversationStore;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns all the attributes currently associated with this context.
     *
     * @return Map
     */
    public function getAttributes(): Map
    {
        return $this->userService->getUserAttributes($this->user);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $attributeName): AttributeInterface
    {
        if ($this->hasAttribute($attributeName)) {
            /** @var UserAttribute $userAttribute */
            $userAttribute = $this->getAttributes()->get($attributeName);
            return $userAttribute->getInternalAttribute();
        } else {
            Log::warning(sprintf("Cannot return attribute with name %s - does not exist", $attributeName));
            throw new AttributeDoesNotExistException(
                sprintf("Cannot return attribute with name %s - does not exist", $attributeName)
            );
        }
    }

    /**
     * @param $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName): bool
    {
        return $this->getAttributes()->hasKey($attributeName);
    }

    /**
     * @inheritDoc
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->userService->addUserAttribute($this->getUser(), $attribute);
    }

    /**
     * Removes an attribute from the user if there is one with the given ID
     *
     * @param string $attributeName
     * @return bool true if removed, false if not
     */
    public function removeAttribute(string $attributeName): bool
    {
        if ($this->hasAttribute($attributeName)) {
            $this->getAttribute($attributeName)->setValue(null);
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
     * @param Conversation $conversationForCloning
     * @param Conversation $conversationForConnecting
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
    public function getCurrentIntent()
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
