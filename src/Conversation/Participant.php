<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Graph\Node\Node;

class Participant extends Node
{

    public function __construct($id)
    {
        parent::__construct();
        $this->setId($id);
    }

    public function says(Intent $intent)
    {
        $this->createOutgoingEdge(Model::SAYS, $intent);
    }

    public function listensFor(Intent $intent)
    {
        $this->createOutgoingEdge(Model::LISTENS_FOR, $intent);
    }

    public function getAllIntentsSaid()
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::SAYS);
    }

    public function getAllIntentsListenedFor()
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::LISTENS_FOR);
    }
}
