<?php

namespace OpenDialogAi\Core\Graph\Edge;

use OpenDialogAi\Core\Graph\Node\Node;

class DirectedEdge extends Edge
{

    /**
     * DirectedEdge constructor.
     * @param $id
     * @param Node $from
     * @param Node $to
     * @param array|null $facets
     */
    public function __construct($id, Node $from, Node $to, array $facets = null)
    {
        parent::__construct($id, $from, $to, $facets);
        $from->addOutgoingEdge($this);
        $to->addIncomingEdge($this);
    }

    /**
     * @return Node
     */
    public function getFromNode()
    {
        return $this->a;
    }

    /**
     * @return Node
     */
    public function getToNode()
    {
        return $this->b;
    }

    /**
     * @param Node $b
     */
    public function setToNode(Node $b)
    {
        $this->b = $b;
    }

    /**
     * @param Node $a
     */
    public function setFromNode(Node $a)
    {
        $this->a = $a;
    }
}
