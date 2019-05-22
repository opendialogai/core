<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\AllOpeningIntents;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class DGraphConversationStore implements ConversationStoreInterface
{
    private $dGraphClient;

    private $attributeResolver;

    public function __construct(DGraphClient $dGraphClient, AttributeResolver $attributeResolver)
    {
        $this->dGraphClient = $dGraphClient;
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * @return Map
     */
    public function getAllOpeningIntents(): Map
    {
        $query = new AllOpeningIntents($this->dGraphClient, $this->attributeResolver);

        return $query->getIntents();
    }

    /**
     * @param $conversationId
     * @return Conversation
     */
    public function getConversation($conversationId): Conversation
    {
        $conversation = ConversationQueryFactory::getConversationFromDGraphWithUid(
            $conversationId,
            $this->dGraphClient,
            true
        );

        return $conversation;
    }

    /**
     * @param $conversationTemplateName
     * @return Conversation
     */
    public function getConversationTemplate($conversationTemplateName): Conversation
    {
        $conversation = ConversationQueryFactory::getConversationFromDGraphWithTemplateName(
            $conversationTemplateName,
            $this->dGraphClient,
            true
        );

        return $conversation;
    }
}
