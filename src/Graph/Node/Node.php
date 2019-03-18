<?php

namespace OpenDialogAi\Core\Graph\Node;


use Ds\Map;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;
use OpenDialogAi\Core\Graph\Edge\DirectedEdge;
use OpenDialogAi\Core\Graph\Edge\Edge;
use OpenDialogAi\Core\Graph\Edge\EdgeSet;
use OpenDialogAi\Core\Graph\GraphItem;

/**
 * Class Node
 * @package OpenDialog\Core\Graph\Node
 *
 * A Node in a graph structure.
 */
class Node
{
    use GraphItem, HasAttributesTrait;

    /* @var Map $outgoingEdges - the set of edges leaving this node keyed by the outgoing relationship name.
     * The structure of the map is [key][EdgeSet]
     */
    private $outgoingEdges;

    /* @var Map $incomingEdges - the set of edges arriving to this node keyed by relationships
     * The structure of the map is [key][EdgeSet]
     */
    private $incomingEdges;

    public function __construct()
    {
        $this->outgoingEdges = new Map();
        $this->incomingEdges = new Map();
        $this->attributes = new Map();
    }

    /**
     * Creates a new directed edge that connects this node to the referenced node.
     *
     * @param $relationship
     * @param Node $node
     * @return DirectedEdge
     */
    public function createOutgoingEdge($relationship, Node $node)
    {
        return new DirectedEdge($relationship, $this, $node);
    }

    /**
     * @param Edge $edge
     */
    public function addOutgoingEdge(Edge $edge)
    {
        $this->addEdges($this->outgoingEdges, $edge);
    }

    /**
     * @param Edge $edge
     */
    public function addIncomingEdge(Edge $edge)
    {
        $this->addEdges($this->incomingEdges, $edge);
    }


    /**
     * @param $edges
     * @param $edge
     */
    private function addEdges(Map $edges, Edge $edge)
    {
        // Check if the edge relationship exists
        if ($edges->hasKey($edge->getId())) {
            /* @var EdgeSet $edgeSet */
            $edgeSet = $edges->get($edge->getId());
            $edgeSet->addEdge($edge);
        } else {
            /* @vat EdgeSet $edgeSet */
            $edgeSet = new EdgeSet($edge->getId());
            $edgeSet->addEdge($edge);
            $edges->put($edge->getId(), $edgeSet);
        }
    }

    /**
     * @param $edge_id
     * @return bool
     */
    public function hasEdgeWithRelationship($relationship)
    {
        if ($this->outgoingEdges->hasKey(($relationship)) || $this->incomingEdges->hasKey($relationship)) {
            return true;
        }

        return false;
    }

    /**
     * @param $edge_id
     * @return bool
     */
    public function hasOutgoingEdgeWithRelationship($relationship)
    {
        if ($this->outgoingEdges->hasKey($relationship)) {
            return true;
        }

        return false;
    }

    /**
     * @param $edge_id
     * @return bool
     */
    public function hasIncomingEdgeWithRelationship($relationship)
    {
        if ($this->incomingEdges->hasKey($relationship)) {
            return true;
        }

        return false;
    }

    /**
     * @param $relationship
     * @return EdgeSet | boolean
     */
    public function getOutgoingEdgesWithRelationship($relationship)
    {
        if ($this->hasOutgoingEdgeWithRelationship($relationship)) {
            return $this->outgoingEdges->get($relationship);
        }

        return false;
    }

    /**
     * @param $relationship
     * @return mixed
     */
    public function getIncomingEdgesWithRelationship($relationship)
    {
        if ($this->incomingEdges->hasKey(($relationship))) {
            return $this->incomingEdges->get($relationship);
        }

        return false;
    }

    /**
     * @param $relationship
     * @return Map
     */
    public function getNodesConnectedByOutgoingRelationship($relationship)
    {
        $nodes = new Map();
        if ($this->hasOutgoingEdgeWithRelationship($relationship)) {
            /* @var EdgeSet $edgesToOpeningScenes */
            $edges = $this->getOutgoingEdgesWithRelationship($relationship);
            $nodes = $edges->getToNodes();
        }

        return $nodes;
    }

    /**
     * @param $relationship
     * @return Map
     */
    public function getNodesConnectedByIncomingRelationship($relationship)
    {
        $nodes = new Map();
        if ($this->hasIncomingEdgeWithRelationship($relationship)) {
            /* @var EdgeSet $edgesToOpeningScenes */
            $edges = $this->getIncomingEdgesWithRelationship($relationship);
            $nodes = $edges->getFromNodes();
        }

        return $nodes;
    }
}
