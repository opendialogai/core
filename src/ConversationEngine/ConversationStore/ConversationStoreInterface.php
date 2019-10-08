<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;

interface ConversationStoreInterface
{
    public function getAllOpeningIntents(): Map;

    public function getConversation($conversationId, $clone = true): Conversation;

    public function getConversationTemplate($conversationTemplateName): Conversation;

    public function getIntentByConversationIdAndOrder($conversationId, $order): Intent;

    public function getIntentByUid($intentUid): Intent;
}
