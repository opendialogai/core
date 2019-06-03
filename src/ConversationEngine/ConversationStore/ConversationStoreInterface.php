<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\Conversation\Conversation;

interface ConversationStoreInterface
{
    public function getAllOpeningIntents(): Map;

    public function getConversation($conversationId): Conversation;

    public function getConversationTemplate($conversationTemplateName): Conversation;
}
