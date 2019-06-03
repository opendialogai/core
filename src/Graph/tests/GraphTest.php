<?php

namespace OpenDialogAi\Core\Graph\Tests;

use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Tests\TestCase;

class GraphTest extends TestCase
{

    public function testNodeCreation()
    {
        $n1 = new Node();
        $n1->setId('test.node1');

        $this->assertTrue($n1->getId() == 'test.node1');
    }

    public function testAttributeAssignment()
    {
        $n1 = new Node();
        $n1->setId('test.node1');

        $a = new StringAttribute('testA', 'testValue');
        $b = new IntAttribute('testB', 3);

        $n1->addAttribute($a);
        $n1->addAttribute($b);

        $aTest = $n1->getAttribute('testA');
        $this->assertSame($a->getId(), $aTest->getId());

        $nodeAttributes = $n1->getAttributes();
        $this->assertTrue(count($nodeAttributes) == 2);
    }

    public function testNodeRelationshipThroughEdge()
    {
        $n1 = new Node();
        $n1->setId('test.node1');
        $n2 = new Node();
        $n2->setId('test.node2');
        $n3 = new Node();
        $n3->setId('test.node3');
        $n4 = new Node();
        $n4->setId('test.node4');

        $n1->createOutgoingEdge('r1', $n2);
        $n1->createOutgoingEdge('r1', $n4);
        $n1->createOutgoingEdge('r3', $n3);

        $this->assertTrue($n1->hasOutgoingEdgeWithRelationship('r1'));
        $this->assertTrue($n2->hasIncomingEdgeWithRelationship('r1'));
        $this->assertTrue($n4->hasIncomingEdgeWithRelationship('r1'));

        $this->assertTrue($n1->hasOutgoingEdgeWithRelationship('r3'));
        $this->assertTrue($n3->hasIncomingEdgeWithRelationship('r3'));

        /* @var \OpenDialog\Core\Graph\Edge\EdgeSet $n1Edges */
        $n1Edges = $n1->getOutgoingEdgesWithRelationship('r1');
        $this->assertTrue(count($n1Edges->getEdges()) == 2);
        $this->assertTrue($n1Edges->getFirstEdge()->getId() == 'r1');


        $fromNodes = $n2->getNodesConnectedByIncomingRelationship('r1');
        $this->assertTrue(count($fromNodes) == 1);
        $this->assertTrue($fromNodes->get('test.node1')->getId() == 'test.node1');

        $fromNodes = $n3->getNodesConnectedByIncomingRelationship('r3');
        $this->assertTrue(count($fromNodes) == 1);
        $this->assertTrue($fromNodes->get('test.node1')->getId() == 'test.node1');
        $this->assertFalse($n3->hasIncomingEdgeWithRelationship('r1'));
    }

}
