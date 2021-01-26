<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use Ds\Map;
use OpenDialogAi\AttributeEngine\ArrayAttribute;
use OpenDialogAi\AttributeEngine\AttributeInterface;
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

        $id = $node->uidIsSet() ? $node->getUid() : $node->hashOrId();
        if ($node->uidIsSet()) {
            $update = true;
        }

        // Add the ID value.
        $attributeStatement[] = $this->prepareAttributeTriple($id, 'id', $node->getId(), $update);

        $nodeType = $this->prepareNodeType($node, $id, $update);

        if ($nodeType) {
            $attributeStatement[] = $nodeType;
        }

        // Add all the attributes related to this node.
        $attributes = $node->getAttributes();
        /* @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            // Skip the UID - we don't need to add that back as an attribute.
            if ($attribute->getId() == Model::UID) {
                continue;
            }

            if ($attribute instanceof ArrayAttribute) {
                $attributeValue = $attribute->toString();
            } else {
                $attributeValue = $attribute->getValue();
            }

            $attributeStatement[] = $this->prepareAttributeTriple(
                $id,
                $attribute->getId(),
                $attributeValue,
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
                $fromId = $node->uidIsSet() ? $node->getUid() : $node->hashOrId();
                if ($node->uidIsSet()) {
                    $updateFrom = true;
                }

                $toId = $edge->getToNode()->uidIsSet() ? $edge->getToNode()->getUid() : $edge->getToNode()->hashOrId();
                if ($edge->getToNode()->uidIsSet()) {
                    $updateTo = true;
                }

                // Add the relationship.
                $relationshipStatement[] = $this->prepareRelationshipTriple(
                    $fromId,
                    $relationship,
                    $toId,
                    $updateFrom,
                    $updateTo,
                    $edge->hasFacets() ? $edge->getFacets() : null
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
            return sprintf('<%s> <%s> "%s" .', $subject, $predicate, $this->escapeCharacters($object));
        }

        $subject = $this->normalizeString($subject);
        return sprintf('_:%s <%s> "%s" .', $subject, $predicate, $this->escapeCharacters($object));
    }

    /**
     * @param $subject
     * @param $predicate
     * @param $object
     * @param bool $updateFrom
     * @param bool $updateTo
     * @param Map|null $facets
     * @return string
     */
    public function prepareRelationshipTriple(
        $subject,
        $predicate,
        $object,
        bool $updateFrom = false,
        bool $updateTo = false,
        Map $facets = null
    ) {
        if ($updateFrom && $updateTo) {
            $prepared = sprintf('<%s> <%s> <%s>', $subject, $predicate, $this->escapeCharacters($object));
        }

        if ($updateFrom && !$updateTo) {
            $object = $this->normalizeString($object);
            $prepared = sprintf('<%s> <%s> _:%s', $subject, $predicate, $this->escapeCharacters($object));
        }

        if (!$updateFrom && $updateTo) {
            $subject = $this->normalizeString($subject);
            $prepared = sprintf('_:%s <%s> <%s>', $subject, $predicate, $this->escapeCharacters($object));
        }

        if (!$updateFrom && !$updateTo) {
            $subject = $this->normalizeString($subject);
            $object = $this->normalizeString($object);
            $prepared = sprintf('_:%s <%s> _:%s', $subject, $predicate, $this->escapeCharacters($object));
        }

        if (is_null($facets)) {
            return sprintf('%s .', $prepared);
        } else {
            return sprintf('%s %s .', $prepared, $this->prepareFacets($facets));
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

    /**
     * Removes non-valid characters from the input
     *
     * @param $input
     * @return string
     */
    private function normalizeString($input): string
    {
        $invalidCharacters = ['@', '+'];

        return str_replace($invalidCharacters, "", $input);
    }

    /**
     * Escapes non-valid characters from the mutation
     *
     * @param $input
     * @return mixed
     */
    private function escapeCharacters($input)
    {
        return str_replace('*', '\*', str_replace("\n", "\\n", $input));
    }

    /**
     * @param Node $node
     * @param string $id
     * @param bool $update
     * @return string|null
     */
    private function prepareNodeType(Node $node, string $id, bool $update): ?string
    {
        if ($node->hasGraphType()) {
            return $this->prepareAttributeTriple($id, 'dgraph.type', $node->getGraphType(), $update);
        } else {
            return null;
        }
    }

    /**
     * @param Map $facets
     * @return string
     */
    public static function prepareFacets(Map $facets): string
    {
        $prepared = '(';
        $prepared .= join(", ", $facets->map(function ($key, $value) use ($facets) {
            return sprintf('%s=%s', $key, is_string($value) ? "\"$value\"" : $value);
        })->toArray());
        $prepared .= ')';

        return $prepared;
    }
}
