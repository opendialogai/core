<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

class IncomingIntentMatcher
{
    const GLOBAL_NO_MATCH_INTENT_ID = 'intent.core.NoMatch';
    const TURN_NO_MATCH_INTENT_ID = 'intent.core.TurnNoMatch';
    const SCENE_NO_MATCH_INTENT_ID = 'intent.core.SceneNoMatch';
    const CONVERSATION_NO_MATCH_INTENT_ID = 'intent.core.ConversationNoMatch';

    /**
     * Returns a single matching incoming intent, determined by the utterance and conversation context. Depending on
     * whether the user is in an ongoing conversation, and whether the user is in a turn request or turn response, will
     * determine how the matching is performed.
     *
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchIncomingIntent(): Intent
    {
        if (ConversationContextUtil::currentConversationUid() == Conversation::UNDEFINED) {
            // It's a non-ongoing conversation
            try {
                return self::matchNextIntentAsStartingRequestIntent();
            } catch (EmptyCollectionException $e) {
                // No matching
                self::prepareContextForStartingRequestGlobalNoMatch();
                return self::matchIncomingIntent();
            }
        } else {
            try {
                // It's an ongoing conversation
                if (ConversationContextUtil::currentIntentIsRequest()) {
                    // if "current" intent (at this point the current data is actually the previous data) is a request it
                    // means we previously dealt with a request
                    return self::matchNextIntentAsResponseIntent();
                } else {
                    return self::matchNextIntentAsRequestIntent();
                }
            } catch (EmptyCollectionException $e) {
                // No matching
                self::prepareContextForOngoingNoMatches();
                return self::matchIncomingIntent();
            }
        }
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    private static function matchNextIntentAsRequestIntent(): Intent
    {
        $scene = SceneSelector::selectSceneById(ConversationContextUtil::currentSceneUid());

        $openTurns = TurnSelector::selectOpenTurns(new SceneCollection([$scene]));
        $turnsWithMatchingValidOrigin = TurnSelector::selectTurnsByValidOrigin(
            new SceneCollection([$scene]),
            ConversationContextUtil::currentIntentId()
        );

        $turns = $openTurns->concat($turnsWithMatchingValidOrigin);
        $turns = $turns->unique(function (Turn $turn) {
            return $turn->getODId();
        });

        $intents = IntentSelector::selectRequestIntents($turns, true, false);

        return IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    private static function matchNextIntentAsResponseIntent(): Intent
    {
        $turn = TurnSelector::selectTurnById(ConversationContextUtil::currentTurnUid());

        $intents = IntentSelector::selectResponseIntents(new TurnCollection([$turn]), true, false);

        return IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    protected static function matchNextIntentAsStartingRequestIntent(): Intent
    {
        $currentScenarioId = ConversationContextUtil::currentScenarioUid();
        $scenarios = new ScenarioCollection();

        if ($currentScenarioId == Scenario::UNDEFINED) {
            $scenarios = ScenarioSelector::selectScenarios(true);
        } else {
            $scenario = ScenarioSelector::selectScenarioById($currentScenarioId, true);
            $scenarios->addObject($scenario);
        }

        // Select valid conversations out of those scenarios - valid conversations will have the "starting" behavior
        // and their conditions will pass.
        $conversations = ConversationSelector::selectStartingConversations($scenarios);

        // Select valid scenes out of the opening conversations - valid scenes will have the "starting" behavior
        // and their conditions will pass
        $scenes = SceneSelector::selectStartingScenes($conversations);

        // Select valid turns out of the opening conversations - valid turns will have the "starting"
        $turns = TurnSelector::selectStartingTurns($scenes);

        // Select valid intents out of the valid turns. Valid intents will match the interpretation and have passing
        // conditions.
        $intents = IntentSelector::selectRequestIntents($turns, true, false);

        // Finally out of all the matching intents select the one with the highest confidence.
        return IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @throws NoMatchingIntentsException
     */
    private static function prepareContextForStartingRequestGlobalNoMatch(): void
    {
        /** @var UtteranceAttribute $utterance */
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);

        if ($utterance->getCallbackId() === self::GLOBAL_NO_MATCH_INTENT_ID) {
            // Even the no-match intent didn't match, quitting (base case)
            throw new NoMatchingIntentsException();
        } elseif ($utterance->getCallbackId() === self::CONVERSATION_NO_MATCH_INTENT_ID) {
            // Conversation no-match intent didn't match, trying global no-match intent
            ContextService::saveAttribute(
                ConversationContext::getComponentId() . '.' . Conversation::CURRENT_CONVERSATION,
                ConversationObject::UNDEFINED
            );
        }

        $utterance->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, self::GLOBAL_NO_MATCH_INTENT_ID);

        ContextService::saveAttribute(
            UserContext::USER_CONTEXT . '.' . UtteranceAttribute::UTTERANCE,
            $utterance
        );
    }

    /**
     * Allows for progressively escalating no-matching from turn no-match, to scene no-match, to conversation no-match,
     * to a global no-match.
     *
     * @throws NoMatchingIntentsException
     */
    private static function prepareContextForOngoingNoMatches(): void
    {
        /** @var UtteranceAttribute $utterance */
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);

        if ($utterance->getCallbackId() === self::TURN_NO_MATCH_INTENT_ID) {
            // Turn no-match intent didn't match, trying scene no-match intent
            $intentId = self::SCENE_NO_MATCH_INTENT_ID;
            ContextService::saveAttribute(
                ConversationContext::getComponentId() . '.' . Intent::INTENT_IS_REQUEST,
                false
            );
        } elseif ($utterance->getCallbackId() === self::SCENE_NO_MATCH_INTENT_ID) {
            // Scene no-match intent didn't match, trying conversation no-match intent
            $intentId = self::CONVERSATION_NO_MATCH_INTENT_ID;
            ContextService::saveAttribute(
                ConversationContext::getComponentId() . '.' . Conversation::CURRENT_CONVERSATION,
                ConversationObject::UNDEFINED
            );
        } else {
            // First try turn no-match intent
            $intentId = self::TURN_NO_MATCH_INTENT_ID;
        }

        $utterance->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $intentId);
        ContextService::saveAttribute(
            UserContext::USER_CONTEXT . '.' . UtteranceAttribute::UTTERANCE,
            $utterance
        );
    }
}
