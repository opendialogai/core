<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;
use OpenDialogAi\OperationEngine\Facade\OperationService;

class IntentFilter
{
    public static function matchIntent(Intent $intent)
    {
        $interpretedIntents = new IntentCollection();

        // Retrieve the utterance we will want to interpreter
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        // Get the interpreter defined in the current model
        $interpreter = $intent->getInterpreter();

        if ($interpreter) {
            $interpretedIntents = InterpreterService::interpret($intent->getInterpreter());
        } else {
            $interpretedIntents = InterpreterService::interpretDefaultInterpreter($utterance);
        }

        $matchingIntents = $interpretedIntents->filter(function (Intent $interpretedIntent, Intent $intent) {
            if ($interpretedIntent->getODId() == $intent->getODId()) {
                // Set the confidence value
                $intent->setConfidence($interpretedIntent->getConfidence());
                // Get any attributes that were identified by the interpreter
                $intent->setAttributes($interpretedIntent->getAttributes());
            }
        });

        return $matchingIntents;
    }
}
