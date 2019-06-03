<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Conversation\Condition\Condition;
use OpenDialogAi\Core\Graph\Edge\EdgeSet;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * Nodes that have conditions should extend this class to ensure we are
 * consistently dealing with Condition representation.
 */
abstract class NodeWithConditions extends Node
{
    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition)
    {
        $this->createOutgoingEdge(Model::HAS_CONDITION, $condition);
    }

    /**
     * @return bool
     */
    public function hasConditions()
    {
        if ($this->hasOutgoingEdgeWithRelationship(Model::HAS_CONDITION)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool | Map
     */
    public function getConditions()
    {
        /* @var EdgeSet @edges */
        $edges =  $this->getOutgoingEdgesWithRelationship(Model::HAS_CONDITION);

        if ($edges) {
            return $edges->getToNodes();
        }

        return false;
    }

    public function getCondition($id)
    {
        $conditions = $this->getConditions();

        if ($conditions->hasKey($id)) {
            return $conditions->get($id);
        }

        return false;
    }
}
