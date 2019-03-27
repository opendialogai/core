<?php


namespace OpenDialogAi\Core\Graph\DGraph;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Graph\Edge\EdgeSet;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Graph\Search\DFS;

/**
 * A DGraph Mutation encapsulates a node (and associated relationships) that should be
 * persisted on DGraph. It uses a Depth-First-Search to identify all related nodes, attributes
 * and relationships and persist them.
 */
class DGraphMutation
{

    /**
     * @var Node $mutationGraph - a starting node to be used to apply a mutation
     * to DGraph.
     */
    private $mutationGraph;

    public function __construct(Node $mutationGraph)
    {
        $this->mutationGraph = $mutationGraph;
    }

    /**
     * @return Node
     */
    public function getMutationGraph()
    {
        return $this->mutationGraph;
    }

    /**
     * Starting from the base node prepare the set of triple statements that will persist the
     * Graph.
     *
     * @return string
     */
    public function prepareTripleMutation()
    {
         /* @var Map $visited - Keeps track of which nodes have been visited in the DFS. */
        $visited = new Map();

        /* Stores the final statement to be POSTed */
        $mutationStatement = "{ set { \r\n";

        // The starting node for traversing the graph.
        $startingNode = $this->mutationGraph;

        DFS::walk($startingNode, $visited, function($startingNode) use (&$mutationStatement) {
            $mutationStatement .= $this->attributeStatement($startingNode) . "\r\n";
            $mutationStatement .= $this->relationshipStatement($startingNode) . "\r\n";
        });

        return $mutationStatement . "}}";
    }

    /**
     * Given a node it prepares appropriate statements to create the node and all associated
     * attributes in DGraph.
     *
     * @param Node $node
     * @return string
     */
    private function attributeStatement(Node $node)
    {
        $attributeStatement = [];

        // Add the ID value.
        $attributeStatement[] = $this->prepareTriple($node->getId(), 'id', $node->getId());

        // Add all the attributes related to this node.
        $attributes = $node->getAttributes();
        /* @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $attributeStatement[] = $this->prepareTriple($node->getId(), $attribute->getId(), $attribute->getValue());
        }

        return implode("\r\n" , $attributeStatement);
    }

    /**
     * @param Node $node
     * @return string
     */
    private function relationshipStatement(Node $node)
    {
        $relationshipStatement = [];

        // Get all the outgoing relationships for the node
        $outgoingEdges = $node->getOutgoingEdges();

        /* @var EdgeSet $edgeSet */
        /* @var DirectedEdge $edge */
        foreach ($outgoingEdges as $relationship => $edgeSet) {
            foreach ($edgeSet->getEdges() as $edge) {
                // Add the relationship.
                $relationshipStatement[] = $this->prepareTriple($node->getId(), $relationship, $edge->getToNode()->getId(), true);
            }
        }

        return implode("\r\n" , $relationshipStatement);
    }

    /**
     * @param string $subject
     * @param string $predicate
     * @param string $object
     * @param bool $relationship
     * @return string
     */
    private function prepareTriple($subject, $predicate, $object, bool $relationship = false)
    {
        if ($relationship) {
            return sprintf('_:%s <%s> _:%s .', $subject, $predicate, $object);
        } else {
            return sprintf('_:%s <%s> "%s" .', $subject, $predicate, $object);
        }
    }

    /**
     * @return false|string
     */
    public function prepareJsonMutation()
    {
        $mutation = [
            'set' => [
                // @todo support Json based mutations
            ]
        ];

        return json_encode($mutation);
    }

}