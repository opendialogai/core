<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;

/**
 * The ConversationalStateReason determines the current conversational state for a given user.
 * It makes user of the context layer and
 * @package OpenDialogAi\ConversationEngine\Reasoners
 */
class ConversationalStateReasoner
{
    /**
     * The user attribute should have the latest conversation record associated with it if this is
     * an existing user or no conversation record if it is a new user.
     * @param UserAttribute $user
     */
    public static function determineConversationalStateForUser(UserAttribute $user)
    {

    }
}
