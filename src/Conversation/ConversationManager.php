<?php

namespace OpenDialogAi\Core\Conversation;

/**
 * A conversation manager assists in managing conversations or creating them from scratch.
 */
class ConversationManager
{
    /* @var Conversation $conversation - the root of the conversation graph */
    private $conversation;

    static $validStateTransitions = [
        Conversation::SAVED => [Conversation::SAVED, Conversation::ACTIVATABLE],
        Conversation::ACTIVATABLE => [Conversation::ACTIVATABLE, Conversation::ACTIVATED],
        Conversation::ACTIVATED => [Conversation::ACTIVATED, Conversation::DEACTIVATED],
        Conversation::DEACTIVATED => [Conversation::DEACTIVATED, Conversation::ACTIVATED, Conversation::ARCHIVED],
        Conversation::ARCHIVED => [Conversation::ARCHIVED, Conversation::DEACTIVATED],
    ];

    public function __construct(
        string $conversation_id,
        string $conversationStatus,
        int $conversationVersion,
        Conversation $existingConversation = null
    ) {
        if (!$existingConversation) {
            $this->conversation = new Conversation($conversation_id, $conversationStatus, $conversationVersion);
        } else {
            $this->conversation = $existingConversation;
        }
    }

    /**
     * Helper function to return a manager for an existing conversation.
     *
     * @param Conversation $conversation
     * @return ConversationManager
     */
    public static function createManagerForExistingConversation(Conversation $conversation)
    {
        $cm = new ConversationManager(
            $conversation->getId(),
            $conversation->getConversationStatus(),
            $conversation->getConversationVersion(),
            $conversation
        );

        return $cm;
    }

    /**
     * @return Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @return int
     */
    public function getConversationVersion(): int
    {
        return $this->conversation->getConversationVersion();
    }

    /**
     * @param $conversationVersion
     */
    public function setConversationVersion($conversationVersion)
    {
        $this->conversation->setConversationVersion($conversationVersion);
    }

    /**
     * @param Condition $condition
     * @return ConversationManager
     */
    public function addConditionToConversation(Condition $condition)
    {
        $this->conversation->addCondition($condition);

        return $this;
    }

    /**
     * @param $id
     * @param bool $opening
     * @return ConversationManager
     */
    public function createScene($id, $opening = false)
    {
        $scene = new Scene($id);

        if ($opening) {
            $this->conversation->addOpeningScene($scene);
        } else {
            $this->conversation->addScene($scene);
        }

        return $this;
    }

    /**
     * @param $id
     * @return bool|Scene
     */
    public function getScene($id)
    {
        return $this->conversation->getScene($id);
    }

    /**
     * @param $sceneId
     * @param Condition $condition
     * @return ConversationManager
     */
    public function addConditionToScene($sceneId, Condition $condition)
    {
        /* @var Scene $scene */
        $scene = $this->conversation->getScene($sceneId);
        $scene->addCondition($condition);

        return $this;
    }


    /**
     * @param $intentId
     * @param Action $action
     * @return $this
     */
    public function addActionToIntent($intentId, Action $action)
    {
        /* @var Intent $intent */
        $intent = $this->conversation->getIntent($intentId);
        $intent->addAction($action);

        return $this;
    }

    /**
     * @param $sceneId
     * @param Intent $intent
     * @param $order
     * @return $this
     */
    public function userSaysToBot($sceneId, Intent $intent, $order)
    {
        // Clone the intent to ensure that we don't have intent nodes pointed to from multiple scenes.
        $sceneIntent = clone $intent;
        $sceneIntent->setOrderAttribute($order);

        /* @var Scene $scene */
        $scene = $this->conversation->getScene($sceneId);
        // Connect the two participants via the intent.
        $scene->userSaysToBot($sceneIntent);

        return $this;
    }

    /**
     * @param $sceneId
     * @param Intent $intent
     * @param $order
     * @return $this
     */
    public function botSaysToUser($sceneId, Intent $intent, $order)
    {
        // Clone the intent to ensure that we don't have intent nodes pointed to from multiple scenes.
        $sceneIntent = clone $intent;
        $sceneIntent->setOrderAttribute($order);

        /* @var Scene $scene */
        $scene = $this->conversation->getScene($sceneId);

        if ($scene) {
            // Connect the two participants via the intent.
            $scene->botSaysToUser($sceneIntent);
        }

        return $this;
    }

    /**
     * @param $startingSceneId
     * @param $endingSceneId
     * @param Intent $intent
     * @param $order
     * @return $this
     */
    public function userSaysToBotAcrossScenes($startingSceneId, $endingSceneId, Intent $intent, $order)
    {
        // Clone the intent to ensure that we don't have intent nodes pointed to from multiple scenes.
        $sceneIntent = clone $intent;
        $sceneIntent->setOrderAttribute($order);

        /* @var Scene $startingScene */
        $startingScene = $this->conversation->getScene($startingSceneId);
        $startingScene->userSaysToBotLeadingOutOfScene($sceneIntent);

        /* @var Scene $endingScene */
        $endingScene = $this->conversation->getScene($endingSceneId);
        $endingScene->botListensToUserFromOtherScene($sceneIntent);

        return $this;
    }

    /**
     * @param $startingSceneId
     * @param $endingSceneId
     * @param Intent $intent
     * @param $order
     * @return $this
     */
    public function botSaysToUserAcrossScenes($startingSceneId, $endingSceneId, Intent $intent, $order)
    {
        // Clone the intent to ensure that we don't have intent nodes pointed to from multiple scenes.
        /* @var Intent $sceneIntent */
        $sceneIntent = clone $intent;
        $sceneIntent->setOrderAttribute($order);

        /* @var Scene $startingScene */
        $startingScene = $this->conversation->getScene($startingSceneId);
        $startingScene->botSaysToUserLeadingOutOfScene($sceneIntent);

        /* @var Scene $endingScene */
        $endingScene = $this->conversation->getScene($endingSceneId);
        $endingScene->userListensToBotFromOtherScene($sceneIntent);

        $sceneIntent->createOutgoingEdge(Model::TRANSITIONS_TO, $endingScene);
        $sceneIntent->createOutgoingEdge(Model::TRANSITIONS_FROM, $startingScene);

        return $this;
    }

    /**
     * @param $toStatus
     * @return array
     */
    private function getValidStartingStatuses($toStatus)
    {
        $filtered = array_filter(self::$validStateTransitions, function ($statuses) use ($toStatus) {
            return in_array($toStatus, $statuses);
        });

        return array_keys($filtered);
    }

    /**
     * @param $toStatus
     * @throws InvalidConversationStatusTransitionException
     */
    private function setStatus($toStatus): void
    {
        if (!in_array($toStatus, array_keys(self::$validStateTransitions))) {
            throw new InvalidConversationStatusTransitionException(
                sprintf("'%s' is not a valid conversation status.", $toStatus)
            );
        }

        if (in_array($toStatus, self::$validStateTransitions[$this->conversation->getConversationStatus()])) {
            $this->conversation->setConversationStatus($toStatus);
        } else {
            throw new InvalidConversationStatusTransitionException(
                sprintf(
                    "Conversations can only transition to '%s' from %s, but this conversation was '%s'",
                    $toStatus,
                    '\'' . join('\', or \'', $this->getValidStartingStatuses($toStatus)) . '\'',
                    $this->conversation->getConversationStatus()
                )
            );
        }
    }

    /**
     * Sets the conversation status to 'activatable' if it is currently 'saved'
     * @throws InvalidConversationStatusTransitionException
     */
    public function setValidated(): void
    {
        $this->setStatus(Conversation::ACTIVATABLE);
    }

    /**
     * Sets the conversation status to 'activated' if it is currently 'activated' or 'deactivated'
     * @throws InvalidConversationStatusTransitionException
     */
    public function setActivated(): void
    {
        $this->setStatus(Conversation::ACTIVATED);
    }

    /**
     * Sets the conversation status to 'deactivated' if it is currently 'activated' or 'archived'
     * @throws InvalidConversationStatusTransitionException
     */
    public function setDeactivated(): void
    {
        $this->setStatus(Conversation::DEACTIVATED);
    }

    /**
     * Sets the conversation status to 'archived' if it is currently 'deactivated'
     * @throws InvalidConversationStatusTransitionException
     */
    public function setArchived(): void
    {
        $this->setStatus(Conversation::ARCHIVED);
    }

    /**
     * @param Conversation $updateOf
     */
    public function setUpdateOf(Conversation $updateOf)
    {
        $this->conversation->setUpdateOf($updateOf);
    }

    /**
     * @param Conversation $instanceOf
     */
    public function setInstanceOf(Conversation $instanceOf)
    {
        $this->conversation->setInstanceOf($instanceOf);
    }
}
