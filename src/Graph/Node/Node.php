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

    /** @var bool $idIsUnique */
    protected static $idIsUnique = true;

    /**
     * @var Map $outgoingEdges - the set of edges leaving this node keyed by the outgoing relationship name.
     * The structure of the map is [key][EdgeSet]. Key represents the relationship name.
     */
    protected $outgoingEdges;

    /**
     * @var Map $incomingEdges - the set of edges arriving to this node keyed by relationships
     * The structure of the map is [key][EdgeSet]. Key represents the relationship name.
     */
    protected $incomingEdges;

    public function __construct($id = null)
    {
        $this->outgoingEdges = new Map();
        $this->incomingEdges = new Map();
        $this->attributes = new Map();

        if (isset($id)) {
            $this->setId($id);
        }
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
     * @param $relationship
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
     * @param $relationship
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
     * @param $relationship
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
     * Returns all outgoing edges.
     *
     * @return Map
     */
    public function getOutgoingEdges()
    {
        return $this->outgoingEdges;
    }

    /**
     * Returns true if node has outgoing edges.
     * @return bool
     */
    public function hasOutgoingEdges()
    {
        if (count($this->outgoingEdges) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Returns all the outgoing relationships from this node.
     *
     * @return \Ds\Set
     */
    public function getOutgoingRelationships()
    {
        return $this->outgoingEdges->keys();
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
     * Returns all incoming edges.
     *
     * @return Map
     */
    public function getIncomingEdges()
    {
        return $this->incomingEdges;
    }

    /**
     * Returns true if node has incoming edges.
     * @return bool
     */
    public function hasIncomingEdges()
    {
        if (count($this->incomingEdges) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Returns all the incoming relationships to this node.
     *
     * @return \Ds\Set
     */
    public function getIncomingRelationships()
    {
        return $this->incomingEdges->keys();
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
     * @param array $relationships
     * @return Map
     */
    public function getNodesConnectedByOutgoingRelationships(array $relationships)
    {
        $nodes = new Map();

        foreach ($relationships as $relationship) {
            $nodes->merge($this->getNodesConnectedByOutoingRelationship($relationship));
        }

        return $nodes;
    }

    /**
     * @return Map
     */
    public function getAllNodesOnOutgoingEdges()
    {
        $nodes = new Map();
        if ($this->hasOutgoingEdges()) {
            foreach ($this->outgoingEdges as $relationship => $edgeSet) {
                $nodes = $nodes->merge($edgeSet->getToNodes());
            }
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

    /**
     * @return Map
     */
    public function getAllNodesFromIncomingEdges()
    {
        $nodes = new Map();
        if ($this->hasIncomingEdges()) {
            foreach ($this->incomingEdges as $relationship => $edgeSet) {
                $nodes->merge($edgeSet->getFromNodes());
            }
        }

        return $nodes;
    }

    /**
     * Returns a hash of the object if ID's aren't unique @see Node::$idIsUnique, otherwise returns the ID
     * @return string
     */
    public function hash(): string
    {
        return static::$idIsUnique ? $this->getId() : hash('SHA256', serialize($this));
    }
}
