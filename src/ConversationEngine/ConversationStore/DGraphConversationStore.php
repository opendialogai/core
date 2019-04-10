<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;


use Ds\Map;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\AllOpeningIntents;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationQueryFactory;
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
     * @return Conversation
     */
    public function getConversation($conversationId): Conversation
    {
        $conversation = ConversationQueryFactory::getConversationFromDgraph($conversationId, $this->dGraphClient, true);
        return $conversation;
    }
}
