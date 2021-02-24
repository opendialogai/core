<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\SceneCollection;

/**
 * The SceneSelector should evaluate conditions against scenes to select
 * which scenes can validly be considered for a user
 */
class SceneSelector
{
    public static function selectStartingScenes($conversations): SceneCollection
    {
        /** @var SceneCollection $scenes */
        $scenes = ConversationDataClient::getAllStartingScenes($conversations);

        /** @var SceneCollection $scenesWithPassingConditions */
        $scenesWithPassingConditions = ConditionFilter::filterObjects($scenes);

        return $scenesWithPassingConditions;
    }
}
