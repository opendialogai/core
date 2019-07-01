<?php

namespace OpenDialogAi\Core\Graph\Tests\DGraph;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Tests\TestCase;

class DGraphTest extends TestCase
{
    /* @var DGraphClient */
    private $dGraphClient;

    /* @var AttributeResolver */
    private $attributeResolver;


    public function setUp(): void
    {
        parent::setUp();

        $this->initDDgraph();

        $this->dGraphClient = $this->app->make(DGraphClient::class);
        $this->attributeResolver = $this->app->make(AttributeResolver::class);
    }

    /**
     * @group local
     */
    public function testDGraphQuery()
    {
        // Create a node and store
        $node = new Node('testNode');
        $mutation = new DGraphMutation($node);
        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $this->dGraphClient->tripleMutation($mutation);
        $this->assertEquals('Success', $mutationResponse->getData()['code']);

        // Now attempt retrieve that node
        $query = new DGraphQuery();
        $query->eq('id', 'testNode')
            ->setQueryGraph([
                'uid',
                'expand(_all_)'
            ]);

        /* @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $this->assertEquals('testNode', $response->getData()[0]['id']);
    }

    /**
     * @group local
     */
    public function testDGraphMutation()
    {
        $nodeName = 'testNode1' . time();
        // Create a node and store with an attribute
        $node = new Node($nodeName);
        $node->addAttribute(new StringAttribute('name', 'John Smith'));
        $mutation = new DGraphMutation($node);
        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $this->dGraphClient->tripleMutation($mutation);
        $this->assertEquals('Success', $mutationResponse->getData()['code']);

        // Now attempt retrieve that node
        $query = new DGraphQuery();
        $query->eq('id', $nodeName)
            ->setQueryGraph([
                'uid',
                'expand(_all_)'
            ]);

        /* @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $this->assertTrue($response->getData()[0]['id'] == $nodeName);

        // Recreate the node
        $node1 = new Node();
        $uid = '';
        foreach ($response->getData()[0] as $name => $value) {
            if ($name == 'id') {
                $node1->setId($value);
                continue;
            }

            if ($name == 'uid') {
                $node1->setUid($value);
                $uid = $value;
                continue;
            }

            try {
                $attribute = $this->attributeResolver->getAttributeFor($name, $value);
                $node1->addAttribute($attribute);
            } catch (AttributeIsNotSupported $e) {
                // Simply skip attributes we can't deal with.
                continue;
            }
        }

        $this->assertTrue($node1->hasAttribute('name'));
        $node1->setAttribute('name', 'Mario Rossi');

        $mutation = new DGraphMutation($node1);
        $mutationResponse = $this->dGraphClient->tripleMutation($mutation);

        // Now retrieve the node using the uid and check that the name is Mario Rossi
        $query = new DGraphQuery();
        $query->uid($uid)
            ->setQueryGraph([
                'uid',
                'expand(_all_)'
            ]);

        /* @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $this->assertTrue($response->getData()[0]['name'] == 'Mario Rossi');
    }
}
