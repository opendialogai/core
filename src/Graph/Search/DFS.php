<?php

namespace OpenDialogAi\Core\Graph\Search;

use Ds\Map;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * Given a starting node this implements a depth-first search of the node.
 */
class DFS
{
    public static function walk(Node $startingNode, Map $visited, callable $callback = null)
    {
        $visited[$startingNode->hashOrId()] = true;

        if (isset($callback)) {
            call_user_func($callback, $startingNode);
        }

        foreach ($startingNode->getAllNodesOnOutgoingEdges() as $key => $node) {
            if (!$visited->hasKey($node->hashOrId())) {
                self::walk($node, $visited, $callback);
            }
        }
    }
}
