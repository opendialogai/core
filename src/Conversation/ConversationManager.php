<?php

namespace OpenDialogAi\Core\Conversation;

/**
 * A conversation manager assists in managing conversations or creating them from scratch.
 */
class ConversationManager
{
    /* @var Conversation $conversation - the root of the conversation graph */
    private $conversation;

    public function __construct(string $conversation_id)
    {
        $this->conversation = new Conversation($conversation_id);
    }

    /**
     * Helper function to return a manager for an existing conversation.
     *
     * @param Conversation $conversation
     * @return ConversationManager
     */
    public static function createManagerForExistingConversation(Conversation $conversation)
    {
        $cm = new ConversationManager($conversation->getId());
        $cm->setConversation($conversation);
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
     * @param Conversation $conversation
     */
    public function setConversation(Conversation $conversation)
    {
        $this->conversation = $conversation;
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
}
