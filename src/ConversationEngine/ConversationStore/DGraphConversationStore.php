<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\AllOpeningIntents;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class DGraphConversationStore implements ConversationStoreInterface
{
    private $dGraphClient;

    public function __construct(DGraphClient $dGraphClient)
    {
        $this->dGraphClient = $dGraphClient;
    }

    /**
     * @return Map
     */
    public function getAllOpeningIntents(): Map
    {
        $query = new AllOpeningIntents($this->dGraphClient);

        return $query->getIntents();
    }

    /**
     * @param $conversationId
     * @param bool $clone
     * @return Conversation
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function getConversation($conversationId, $clone = true): Conversation
    {
        $conversation = ConversationQueryFactory::getConversationFromDGraphWithUid(
            $conversationId,
            $this->dGraphClient,
            $clone
        );

        return $conversation;
    }

    /**
     * @param $conversationTemplateName
     * @return Conversation
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
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

    /**
     * Gets the intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param $order
     * @return Intent
     */
    public function getIntentByConversationIdAndOrder($conversationId, $order): Intent
    {
        return ConversationQueryFactory::getConversationIntentByOrder(
            $conversationId,
            $order,
            $this->dGraphClient
        );
    }

    public function getIntentByUid($intentUid): Intent
    {
        return ConversationQueryFactory::getIntentByUid($intentUid, $this->dGraphClient);
    }
}
