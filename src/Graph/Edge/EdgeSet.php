<?php

namespace OpenDialogAi\Core\Graph\Edge;

use Ds\Map;
use Ds\Set;

/**
 * A set of edges with a common relationship.
 *
 * Class EdgeSet
 * @package OpenDialog\Core\Graph\Edge
 */
class EdgeSet
{
    /* @var string $relationshipId */
    private $relationshipId;

    /* @var Set $edges */
    private $edges;

    /**
     * EdgeSet constructor.
     * @param $relationshipId
     */
    public function __construct($relationshipId)
    {
        $this->relationshipId = $relationshipId;
        $this->edges = new Set();
    }

    /**
     * @param Edge $edge
     */
    public function addEdge(Edge $edge)
    {
        $this->edges->add($edge);
    }

    /**
     * @return mixed
     */
    public function getFirstEdge()
    {
        return $this->edges->first();
    }

    /**
     * @return array
     */
    public function getEdges()
    {
        return $this->edges->toArray();
    }

    /**
     * @return Map
     */
    public function getToNodes()
    {
        $toNodes = new Map();

        /* @var DirectedEdge $edge */
        foreach ($this->edges as $edge) {
            $toNodes->put($edge->getToNode()->hash(), $edge->getToNode());
        }

        return $toNodes;
    }

    /**
     * @return Map
     */
    public function getFromNodes()
    {
        $fromNodes = new Map();

        /* @var DirectedEdge $edge */
        foreach ($this->edges as $edge) {
            $fromNodes->put($edge->getFromNode()->getId(), $edge->getFromNode());
        }

        return $fromNodes;
    }
}
