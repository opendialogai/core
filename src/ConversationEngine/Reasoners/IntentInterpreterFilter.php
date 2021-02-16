<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;

class IntentInterpreterFilter
{
    public static function filter($intents, $utterance): IntentCollection
    {
        $interpretedIntents = $intents->map(function (Intent $intent) use ($utterance) {
            // Get the interpreter defined in the current model
            $interpreter = $intent->getInterpreter();
            $interpretedIntents = new IntentCollection();

            if ($interpreter) {
                $interpretations = InterpreterService::interpret($intent->getInterpreter(), $utterance);
            } else {
                $interpretations = InterpreterService::interpretDefaultInterpreter($utterance);
            }
            $intent->addInterpretedIntents($interpretations);
            return $intent;
        });

        // With the interpretations in place let us do a match
        $matchingIntents = $interpretedIntents->filter(function ($intent) {
            return $intent->checkForMatch();
        });

        return $matchingIntents;
    }
}
