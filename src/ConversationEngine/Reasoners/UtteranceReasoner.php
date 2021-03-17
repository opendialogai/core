<?php

namespace OpenDialogAi\ConversationEngine\Reasoners;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\CouldNotCreateUserFromUtteranceException;
use OpenDialogAi\ConversationEngine\Exceptions\IncomingUtteranceNotValid;

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
            ContextService::loadPersistentContextAttributes($utterance->getUserId());

            // Save the utterance
            ContextService::saveAttribute(
                UserContext::USER_CONTEXT.'.'.UtteranceAttribute::UTTERANCE,
                $utterance
            );

            // Save the utterance user
            ContextService::saveAttribute(
                UserContext::USER_CONTEXT.'.'.UtteranceAttribute::UTTERANCE_USER,
                $utterance->getAttribute(UtteranceAttribute::UTTERANCE_USER)
            );

            // Retrieve the current user
            /* @var UserAttribute $currentUser */
            $currentUser = ContextService::getAttribute(
                UserAttribute::CURRENT_USER, UserContext::USER_CONTEXT
            );
            if ($currentUser->getUserId() == '') {
                throw new CouldNotCreateUserFromUtteranceException(
                    "The user has not user id set - could not create a new user or update an existing one
                     using utterance information."
                );
            }
            return $currentUser;
        } else {
            throw new IncomingUtteranceNotValid('Utterance provided cannot be used');
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
