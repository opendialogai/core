<?php

namespace OpenDialogAi\Core\Graph\Edge;

use OpenDialogAi\Core\Graph\Node\Node;

/**
 * Class DirectedEdge
 * @package OpenDialogAi\Core\Graph\Edge
 */
class DirectedEdge extends Edge
{

    /**
     * DirectedEdge constructor.
     * @param $id
     * @param Node $from
     * @param Node $to
     */
    public function __construct($id, Node $from, Node $to)
    {
        parent::__construct($id, $from, $to);
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
