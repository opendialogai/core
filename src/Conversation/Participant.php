<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Graph\Node\Node;

/**
 * A participant is a user or a software agent participating in a conversation.
 */
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
