<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;

interface ConversationStoreInterface
{
    public function getAllOpeningIntents(): EIModelOpeningIntents;

    public function getConversation($conversationId, $clone = true): EIModelConversation;

    public function getConversationTemplate($conversationTemplateName): EIModelConversation;

    public function getIntentByConversationIdAndOrder($conversationId, $order): EIModelIntent;

    public function getIntentByUid($intentUid): EIModelIntent;
}
