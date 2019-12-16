<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

interface ConversationQueryFactoryInterface
{
    /**
     * @return DGraphQuery
     */
    public static function getAllOpeningIntents(): DGraphQuery;

    /**
     * @return DGraphQuery
     */
    public static function getConversationTemplateIds(): DGraphQuery;

    /**
     * @param string $conversationUid
     * @return DGraphQuery
     */
    public static function getConversationFromDGraphWithUid(string $conversationUid): DGraphQuery;

    /**
     * @param string $templateName
     * @return DGraphQuery
     */
    public static function getConversationFromDGraphWithTemplateName(string $templateName): DGraphQuery;

    /**
     * @param string $templateName
     * @return DGraphQuery
     */
    public static function getConversationTemplateUid(string $templateName): DGraphQuery;

    /**
     * Gets a user conversation by uid
     *
     * @param string $conversationId
     * @return DGraphQuery
     */
    public static function getUserConversation(string $conversationId): DGraphQuery;

    /**
     * Gets an intent by uid
     *
     * @param string $intentUid
     * @return DGraphQuery
     */
    public static function getIntentByUid(string $intentUid): DGraphQuery;

    /**
     * Returns UID's if a conversation has been used before.
     *
     * @param string $name
     * @return DGraphQuery
     */
    public static function hasConversationBeenUsed(string $name): DGraphQuery;
}
