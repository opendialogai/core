<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;


use Ds\Map;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\AllOpeningIntents;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

class DGraphConversationStore implements ConversationStoreInterface
{
    private $dGraphClient;

    public function __construct(DGraphClient $dGraphClient){
        $this->dGraphClient = $dGraphClient;
    }

    public function getAllOpeningIntents(): Map
    {
        $query = new AllOpeningIntents($this->dGraphClient);

        return $query->getIntents();
    }

}