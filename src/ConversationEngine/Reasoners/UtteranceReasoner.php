<?php

namespace OpenDialogAi\ConversationEngine\Reasoners;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;

/**
 * The UtteranceReasoner analyses an incoming utterance and interacts with the context layer to provide a
 * user attribute that can be fed into the other steps.
 *
 * The UtteranceReasoner should be where we look for any platform specific information that should be fed
 * to the context layer to influence subsequent steps or deal with any edge cases around how we handle incoming
 * utterances.
 */
class UtteranceReasoner
{
    public static function analyseUtterance(UtteranceAttribute $utterance): Attribute
    {
        if (UtteranceReasoner::utteranceIsValid($utterance)) {
            // Retrieve the user context,  scope it to the incoming user_id and retrieve the current user
            $userContext = ContextService::getContext('user');
            $userContext->addAttribute($utterance->getAttribute(UtteranceAttribute::UTTERANCE_USER));
            $userContext->setScope(['user_id' => $utterance->getUserId()]);

            /* UserAttribute $currentUser */
            $currentUser = $userContext->getAttribute(UserAttribute::CURRENT_USER);
            return $currentUser;
        }
    }

    public static function utteranceIsValid(UtteranceAttribute $utterance): bool
    {
        // Check that the utterance has a userId
        if (!$utterance->hasAttribute(UtteranceAttribute::UTTERANCE_USER_ID)) {
            Log::alert('User ID missing from incoming utterance.');
            return false;
        }
        return true;
    }
}
