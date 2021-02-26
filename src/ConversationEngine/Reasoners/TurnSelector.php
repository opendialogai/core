<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\SceneCollection;
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
     */
    public static function selectStartingTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        /** @var TurnCollection $turns */
        $turns = ConversationDataClient::getAllStartingTurns($scenes);

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
     */
    public static function selectOpenTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     *
     * Retrieves all turns within the given scenes
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     */
    public static function selectTurns(SceneCollection $scenes, bool $shallow = true): TurnCollection
    {
        return new TurnCollection();
    }
}
