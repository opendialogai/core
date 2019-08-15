<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
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

    /**
     * @param Intent $intent
     */
    public function says(Intent $intent)
    {
        $this->createOutgoingEdge(Model::SAYS, $intent);
    }

    /**
     * @param Intent $intent
     */
    public function listensFor(Intent $intent)
    {
        $this->createOutgoingEdge(Model::LISTENS_FOR, $intent);
    }

    /**
     * @param Intent $intent
     */
    public function saysAcrossScenes(Intent $intent)
    {
        $this->createOutgoingEdge(Model::SAYS_ACROSS_SCENES, $intent);
    }

    /**
     * @param Intent $intent
     */
    public function listensForAcrossScenes(Intent $intent)
    {
        $this->createOutgoingEdge(Model::LISTENS_FOR_ACROSS_SCENES, $intent);
    }

    /**
     * @return \Ds\Map
     */
    public function getAllIntentsSaid()
    {
        $allIntentsSaid = new Map();
        $allIntentsSaid = $allIntentsSaid->merge(
            $this->getNodesConnectedByOutgoingRelationship(Model::SAYS)
        );
        $allIntentsSaid = $allIntentsSaid->merge(
            $this->getNodesConnectedByOutgoingRelationship(Model::SAYS_ACROSS_SCENES)
        );

        return $allIntentsSaid;
    }

    /**
     * @return \Ds\Map
     */
    public function getAllIntentsSaidInOrder()
    {
        return $this->getAllIntentsSaid()->sorted(function (Node $a, Node $b) {
            return $a->getAttributeValue(Model::ORDER) > $b->getAttributeValue(Model::ORDER);
        });
    }

    /**
     * @return \Ds\Map
     */
    public function getAllIntentsListenedFor()
    {
        $allIntentsSaid = new Map();
        $allIntentsSaid = $allIntentsSaid->merge(
            $this->getNodesConnectedByOutgoingRelationship(Model::LISTENS_FOR)
        );
        $allIntentsSaid = $allIntentsSaid->merge(
            $this->getNodesConnectedByOutgoingRelationship(Model::LISTENS_FOR_ACROSS_SCENES)
        );

        return $allIntentsSaid;
    }

    /**
     * @return bool
     */
    public function isBot()
    {
        if (preg_match("/".Model::BOT."/", $this->id)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        if (preg_match("/".Model::USER."/", $this->id)) {
            return true;
        }
        return false;
    }
}
