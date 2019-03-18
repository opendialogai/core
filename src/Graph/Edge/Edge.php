<?php


namespace OpenDialogAi\Core\Graph\Edge;


use Ds\Map;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;
use OpenDialogAi\Core\Graph\GraphItem;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * Class Edge
 * @package OpenDialogAi\Core\Graph\Edge
 */
class Edge
{
    use GraphItem, HasAttributesTrait;

    /* @var Node $a - a node at one side of the edge */
    protected $a;

    /* @var Node $b - a node at the other side of the edge */
    protected $b;

    /**
     * Edge constructor.
     * @param $id
     * @param Node $a
     * @param Node $b
     */
    public function __construct($id, Node $a, Node $b)
    {
        $this->id = $id;
        $this->a = $a;
        $this->b = $b;

        $this->attributes = new Map();
    }
}
