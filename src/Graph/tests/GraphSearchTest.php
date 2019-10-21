<?php

namespace OpenDialogAi\Core\Graph\Tests;

use Ds\Map;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Graph\Search\DFS;
use OpenDialogAi\Core\Tests\TestCase;

class GraphSearchTest extends TestCase
{
    public function createGraph()
    {
        $a = new Node('a');
        $a->addAttribute(new StringAttribute('type', 'standard'));
        $b = new Node('b');
        $c = new Node('c');
        $d = new Node('d');
        $e = new Node('e');
        $f = new Node('f');

        $a->createOutgoingEdge('rel1', $b);
        $a->createOutgoingEdge('rel2', $d);
        $b->createOutgoingEdge('rel3', $e);
        $b->createOutgoingEdge('rel4', $c);
        $c->createOutgoingEdge('rel5', $a);
        $c->createOutgoingEdge('rel6', $f);
        $e->createOutgoingEdge('rel7', $d);
        $e->createOutgoingEdge('rel8', $f);
        $f->createOutgoingEdge('rel9', $d);

        return $a;
    }

    public function testGraphWalk()
    {
        $startingNode = $this->createGraph();
        $visited = new Map();

        $setMutation = '';

        DFS::walk($startingNode, $visited);

        $this->assertTrue(count($visited) == 6);
        $this->assertTrue($visited->hasKey('a'));
        $this->assertTrue($visited->hasKey('b'));
        $this->assertTrue($visited->hasKey('c'));
        $this->assertTrue($visited->hasKey('d'));
        $this->assertTrue($visited->hasKey('e'));
        $this->assertTrue($visited->hasKey('f'));
    }
}
