<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\SceneCollection;

/**
 * This selector provides methods that select various types of scenes and filters them by evaluating their conditions
 */
class SceneSelector
{
    /**
     * Retrieves all scenes that have the starting behaviour, within the given conversations
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public static function selectStartingScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        /** @var SceneCollection $scenes */
        $scenes = ConversationDataClient::getAllStartingScenes($conversations);

        /** @var SceneCollection $scenesWithPassingConditions */
        $scenesWithPassingConditions = ConditionFilter::filterObjects($scenes);

        return $scenesWithPassingConditions;
    }

    /**
     * Retrieves all scenes that have the open behaviour, within the given conversations
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public static function selectOpenScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieves all scenes within the given conversations
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public static function selectScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        return new SceneCollection();
    }
}
