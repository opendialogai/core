<?php


namespace OpenDialogAi\ConversationEngine\Selectors;

use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\ConversationEngine\Util\SelectorUtil;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * This selector provides methods that select various types of turns and filters them by evaluating their conditions
 */
class TurnSelector
{
    /**
     * Retrieves all turns that have the starting behaviour, within the given scenes
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     * @throws EmptyCollectionException
     */
    public static function selectStartingTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenes);

        $turns = ConversationDataClient::getAllStartingTurns($scenes, $shallow);

        /** @var TurnCollection $turnsWithPassingConditions */
        $turnsWithPassingConditions = ConditionFilter::filterObjects($turns);

        return $turnsWithPassingConditions;
    }

    /**
     *
     * Retrieves all turns that have the open behaviour, within the given scenes
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     * @throws EmptyCollectionException
     */
    public static function selectOpenTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenes);

        $turns = ConversationDataClient::getAllOpenTurns($scenes, $shallow);

        /** @var TurnCollection $turnsWithPassingConditions */
        $turnsWithPassingConditions = ConditionFilter::filterObjects($turns);

        return $turnsWithPassingConditions;
    }

    /**
     *
     * Retrieves all turns within the given scenes
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     * @throws EmptyCollectionException
     */
    public static function selectTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenes);

        $turns = ConversationDataClient::getAllTurns($scenes, $shallow);

        /** @var TurnCollection $turnsWithPassingConditions */
        $turnsWithPassingConditions = ConditionFilter::filterObjects($turns);

        return $turnsWithPassingConditions;
    }

    /**
     * Retrieves all turns within the given scenes by matching valid origin
     *
     * @param SceneCollection $scenes
     * @param string $validOrigin
     * @param bool $shallow
     * @return TurnCollection
     * @throws EmptyCollectionException
     */
    public static function selectTurnsByValidOrigin(
        SceneCollection $scenes,
        string $validOrigin,
        bool $shallow = true
    ): TurnCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($scenes);

        $turns = ConversationDataClient::getAllTurnsByValidOrigin($scenes, $validOrigin, $shallow);

        /** @var TurnCollection $turnsWithPassingConditions */
        $turnsWithPassingConditions = ConditionFilter::filterObjects($turns);

        return $turnsWithPassingConditions;
    }

    /**
     * Retrieves a specific turn
     *
     * @param string $turnId
     * @param bool $shallow
     * @return Turn
     */
    public static function selectTurnById(string $turnId, bool $shallow = true): Turn
    {
        return ConversationDataClient::getTurnById($turnId, $shallow);
    }
}
