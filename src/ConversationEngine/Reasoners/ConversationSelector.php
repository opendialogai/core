<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Util\SelectorUtil;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * This selector provides methods that select various types of conversations and filters them by evaluating their conditions
 */
class ConversationSelector
{
    /**
     * Retrieves all conversations that have the starting behaviour, within the given scenarios
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     * @throws EmptyCollectionException
     */
    public static function selectStartingConversations(
        ScenarioCollection $scenarios,
        bool $shallow = true
    ): ConversationCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenarios);

        $conversations = ConversationDataClient::getAllStartingConversations($scenarios, $shallow);

        /** @var ConversationCollection $conversationsWithPassingConditions */
        $conversationsWithPassingConditions = ConditionFilter::filterObjects($conversations);

        return $conversationsWithPassingConditions;
    }

    /**
     * Retrieves all conversations that have the open behaviour, within the given scenarios
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     * @throws EmptyCollectionException
     */
    public static function selectOpenConversations(
        ScenarioCollection $scenarios,
        bool $shallow = true
    ): ConversationCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenarios);

        $conversations = ConversationDataClient::getAllOpenConversations($scenarios, $shallow);

        /** @var ConversationCollection $conversationsWithPassingConditions */
        $conversationsWithPassingConditions = ConditionFilter::filterObjects($conversations);

        return $conversationsWithPassingConditions;
    }

    /**
     * Retrieves all conversations within the given scenarios
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     * @throws EmptyCollectionException
     */
    public static function selectConversations(ScenarioCollection $scenarios, bool $shallow = true): ConversationCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenarios);

        $conversations = ConversationDataClient::getAllConversations($scenarios, $shallow);

        /** @var ConversationCollection $conversationsWithPassingConditions */
        $conversationsWithPassingConditions = ConditionFilter::filterObjects($conversations);

        return $conversationsWithPassingConditions;
    }
}
