<?php


namespace OpenDialogAi\ConversationEngine\Selectors;

use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\ConversationEngine\Util\SelectorUtil;
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
     * @throws EmptyCollectionException
     */
    public static function selectStartingScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($conversations);

        $scenes = ConversationDataClient::getAllStartingScenes($conversations, $shallow);

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
     * @throws EmptyCollectionException
     */
    public static function selectOpenScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($conversations);

        $scenes = ConversationDataClient::getAllOpenScenes($conversations, $shallow);

        /** @var SceneCollection $scenesWithPassingConditions */
        $scenesWithPassingConditions = ConditionFilter::filterObjects($scenes);

        return $scenesWithPassingConditions;
    }

    /**
     * Retrieves all scenes within the given conversations
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     * @throws EmptyCollectionException
     */
    public static function selectScenes(ConversationCollection $conversations, bool $shallow = true): SceneCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($conversations);

        $scenes = ConversationDataClient::getAllScenes($conversations, $shallow);

        /** @var SceneCollection $scenesWithPassingConditions */
        $scenesWithPassingConditions = ConditionFilter::filterObjects($scenes);

        return $scenesWithPassingConditions;
    }
}
