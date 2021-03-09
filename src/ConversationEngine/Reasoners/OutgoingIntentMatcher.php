<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;

class OutgoingIntentMatcher
{
    /**
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchOutgoingIntent(): Intent
    {
        $scenario = ScenarioSelector::selectScenarioById(ConversationContextUtil::currentScenarioId(), true);

        try {
            $conversation = ConversationSelector::selectConversationById(
                new ScenarioCollection([$scenario]),
                ConversationContextUtil::currentConversationId(),
                true
            );

            $scene = SceneSelector::selectSceneById(
                new ConversationCollection([$conversation]),
                ConversationContextUtil::currentSceneId(),
                true
            );

            $turn = TurnSelector::selectTurnById(
                new SceneCollection([$scene]),
                ConversationContextUtil::currentTurnId(),
                true
            );

            $intents = IntentSelector::selectResponseIntents(new TurnCollection([$turn]), false);

            if ($intents->isEmpty()) {
                throw new NoMatchingIntentsException();
            }

            return $intents->first();
        } catch (EmptyCollectionException $e) {
            Log::debug('No opening intent selected');
            throw new NoMatchingIntentsException();
        }
    }
}
