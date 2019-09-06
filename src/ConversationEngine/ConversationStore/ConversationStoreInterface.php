<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;

interface ConversationStoreInterface
{
    /**
     * @return EIModelOpeningIntents
     * @throws EIModelCreatorException
     */
    public function getAllOpeningIntents(): EIModelOpeningIntents;

    /**
     * @param $conversationId
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getConversation($conversationId): EIModelConversation;

    /**
     * @param $conversationTemplateName
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getConversationTemplate($conversationTemplateName): EIModelConversation;

    /**
     * Gets the opening intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param int $order
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getOpeningIntentByConversationIdAndOrder($conversationId, int $order): EIModelIntent;

    /**
     * @param $intentUid
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getIntentByUid($intentUid): EIModelIntent;
}
