<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;

interface ConversationStoreInterface
{
    /**
     * @return EIModelOpeningIntents
     * @throws EIModelCreatorException
     */
    public function getAllEIModelOpeningIntents(): EIModelOpeningIntents;

    /**
     * @param $conversationId
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getEIModelConversation($conversationId): EIModelConversation;

    /**
     * @param $conversationId
     * @return Conversation
     * @throws EIModelCreatorException
     */
    public function getConversation($conversationId): Conversation;

    /**
     * @param $conversationTemplateName
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getEIModelConversationTemplate($conversationTemplateName): EIModelConversation;

    /**
     * Gets the opening intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param int $order
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getEIModelOpeningIntentByConversationIdAndOrder($conversationId, int $order): EIModelIntent;

    /**
     * Gets the opening intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param int $order
     * @return Intent
     * @throws EIModelCreatorException
     */
    public function getOpeningIntentByConversationIdAndOrder($conversationId, int $order): Intent;

    /**
     * @param $intentUid
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getEIModelIntentByUid($intentUid): EIModelIntent;

    /**
     * @param $intentUid
     * @return Intent
     * @throws EIModelCreatorException
     */
    public function getIntentByUid($intentUid): Intent;

    /**
     * @return EIModelToGraphConverter
     */
    public function getConversationConverter(): EIModelToGraphConverter;
}
