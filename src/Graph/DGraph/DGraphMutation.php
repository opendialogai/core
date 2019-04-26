<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\Edge\DirectedEdge;
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

        DFS::walk($startingNode, $visited, function ($startingNode) use (&$mutationStatement) {
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
        $update = false;

        $id = $node->uidIsSet() ? $node->getUid() : $node->getId();
        if ($node->uidIsSet()) {
            $update = true;
        }

        // Add the ID value.
        $attributeStatement[] = $this->prepareAttributeTriple($id, 'id', $node->getId(), $update);

        // Add all the attributes related to this node.
        $attributes = $node->getAttributes();
        /* @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            // Skip the UID - we don't need to add that back as an attribute.
            if ($attribute->getId() == Model::UID) {
                continue;
            }

            $attributeStatement[] = $this->prepareAttributeTriple(
                $id,
                $attribute->getId(),
                $attribute->getValue(),
                $update
            );
        }

        return implode("\n", $attributeStatement);
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
                $updateFrom = false;
                $updateTo = false;

                // Determine what IDs to use based on whether the nodes have uids set or not.
                $fromId = $node->uidIsSet() ? $node->getUid() : $node->getId();
                if ($node->uidIsSet()) {
                    $updateFrom = true;
                }

                $toId = $edge->getToNode()->uidIsSet() ? $edge->getToNode()->getUid() : $edge->getToNode()->getId();
                if ($edge->getToNode()->uidIsSet()) {
                    $updateTo = true;
                }

                // Add the relationship.
                $relationshipStatement[] = $this->prepareRelationshipTriple(
                    $fromId,
                    $relationship,
                    $toId,
                    $updateFrom,
                    $updateTo
                );
            }
        }

        return implode("\n", $relationshipStatement);
    }

    /**
     * @param string $subject
     * @param string $predicate
     * @param string $object
     * @param bool $update
     * @return string
     */
    private function prepareAttributeTriple($subject, $predicate, $object, bool $update = false)
    {
        if ($update) {
            return sprintf('<%s> <%s> "%s" .', $subject, $predicate, $object);
        } else {
            $subject = $this->normalizeString($subject);
            return sprintf('_:%s <%s> "%s" .', $subject, $predicate, $object);
        }
    }

    /**
     * @param $subject
     * @param $predicate
     * @param $object
     * @param bool $updateFrom
     * @param bool $updateTo
     * @return string
     */
    public function prepareRelationshipTriple(
        $subject,
        $predicate,
        $object,
        bool $updateFrom = false,
        bool $updateTo = false
    ) {
        if ($updateFrom && $updateTo) {
            return sprintf('<%s> <%s> <%s> .', $subject, $predicate, $object);
        }

        if ($updateFrom && !$updateTo) {
            $object = $this->normalizeString($object);
            return sprintf('<%s> <%s> _:%s .', $subject, $predicate, $object);
        }

        if (!$updateFrom && $updateTo) {
            $subject = $this->normalizeString($subject);
            return sprintf('_:%s <%s> <%s> .', $subject, $predicate, $object);
        }

        if (!$updateFrom && !$updateTo) {
            $subject = $this->normalizeString($subject);
            $object = $this->normalizeString($object);
            return sprintf('_:%s <%s> _:%s .', $subject, $predicate, $object);
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

    private function normalizeString($input): string
    {
        $invalidCharacters = ['@'];

        return str_replace($invalidCharacters, "", $input);

    }
}
