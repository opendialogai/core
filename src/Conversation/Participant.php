<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * A participant is a user or a software agent participating in a conversation.
 */
class Participant extends Node
{

    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::PARTICIPANT));
    }

    public function says(Intent $intent)
    {
        $this->createOutgoingEdge(Model::SAYS, $intent);
    }

    public function listensFor(Intent $intent)
    {
        $this->createOutgoingEdge(Model::LISTENS_FOR, $intent);
    }

    public function saysAcrossScenes(Intent $intent)
    {
        $this->createOutgoingEdge(Model::SAYS_ACROSS_SCENES, $intent);
    }

    public function listensForAcrossScenes(Intent $intent)
    {
        $this->createOutgoingEdge(Model::LISTENS_FOR_ACROSS_SCENES, $intent);
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
