<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserHistoryRecord;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

/**
 * The ConversationalStateReason determines the current conversational state for a given user.
 * It makes use of the context layer and looks for a UserHistoryRecord attached to the user.
 */
class ConversationalStateReasoner
{
    public const CONVERSATION_CONTEXT = 'conversation';

    /**
     * The user attribute should have the latest conversation record associated with it if this is
     * an existing user or no conversation record if it is a new user.
     * @param UserAttribute $user
     */
    public static function determineConversationalStateForUser(UserAttribute $user): void
    {
        if ($user->hasAttribute(UserAttribute::USER_HISTORY_RECORD)) {
            $record = $user->getUserHistoryRecord();
            if ($record->completedConversation()) {
                // We setup the conversation context because the conversational state will be changing
                // as we move through conversation reasoning. So rather than pulling relevant values from
                // the history record (which is just a snapshot in time) we can use the conversation context
                // to track where our "current thinking" is. Once a reasoning cycle is done we then extract
                // information from the conversation context to put it back into a history record.
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Scenario::CURRENT_SCENARIO,
                    $record->getScenarioId()
                );
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Conversation::CURRENT_CONVERSATION,
                    ConversationObject::UNDEFINED
                );
            } else {
                // If the conversation is not complete we populate the conversation context with all the
                // relevant info
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Scenario::CURRENT_SCENARIO,
                    $record->getScenarioId()
                );
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Conversation::CURRENT_CONVERSATION,
                    $record->getConversationId()
                );
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Scene::CURRENT_SCENE,
                    $record->getSceneId()
                );
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Turn::CURRENT_TURN,
                    $record->getTurnId()
                );
                ContextService::saveAttribute(
                    self::CONVERSATION_CONTEXT.'.'.Intent::CURRENT_INTENT,
                    $record->getIntentId()
                );
            }
        }

        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Scenario::CURRENT_SCENARIO,
            ConversationObject::UNDEFINED
        );
        ContextService::saveAttribute(
            self::CONVERSATION_CONTEXT.'.'.Conversation::CURRENT_CONVERSATION,
            ConversationObject::UNDEFINED
        );
    }

    public static function setConversationalStateForUser(UserAttribute $user): void
    {
        $record = new UserHistoryRecord(UserHistoryRecord::USER_HISTORY_RECORD);

        $record->setUserHistoryRecordAttribute(
            UserHistoryRecord::SCENARIO_ID,
            ConversationContextUtil::currentScenarioId()
        );

        $record->setUserHistoryRecordAttribute(
            UserHistoryRecord::CONVERSATION_ID,
            ConversationContextUtil::currentConversationId()
        );

        $record->setUserHistoryRecordAttribute(
            UserHistoryRecord::SCENE_ID,
            ConversationContextUtil::currentSceneId()
        );

        $record->setUserHistoryRecordAttribute(
            UserHistoryRecord::TURN_ID,
            ConversationContextUtil::currentTurnId()
        );

        $record->setUserHistoryRecordAttribute(
            UserHistoryRecord::INTENT_ID,
            ConversationContextUtil::currentIntentId()
        );

        $user->setUserHistoryRecord($record);
    }
}
