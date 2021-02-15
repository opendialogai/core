<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;

/**
 * The TurnSelector should evaluate conditions against turns to select
 * which turns can validly be considered for a user
 */
class StartingIntentSelector
{
    public static function selectStartingIntents($turns): IntentCollection
    {
        // These are all the possible intents that could start a conversation
        $intents = ConversationDataClient::getAllStartingIntents($turns);

        // We first reduce the set to just those that have passing conditions
        $conditionPassingIntents = $intents->filter(function ($intent) {
            ConditionFilter::checkConditions($intent);
        });

        // Now we can pass each intent through interpreters and interpret given the utterance
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        $interpretedIntents = $conditionPassingIntents->map(function (Intent $intent) use ($utterance) {
            // Get the interpreter defined in the current model
            $interpreter = $intent->getInterpreter();
            $interpretedIntents = new IntentCollection();

            if ($interpreter) {
                $interpretedIntents = InterpreterService::interpret($intent->getInterpreter(), $utterance);
            } else {
                $interpretedIntents = InterpreterService::interpretDefaultInterpreter($utterance);
            }
            $intent->addInterpretedIntents($interpretedIntents);
        });

        // With the interpretations in place let us do a match
        $matchingIntents = $interpretedIntents->filter(function (Intent $intent){
            return $intent->checkForMatch();
        });

        return $matchingIntents;
    }
}
