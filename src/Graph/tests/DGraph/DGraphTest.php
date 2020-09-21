<?php

namespace OpenDialogAi\Core\Graph\Tests\DGraph;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;
use OpenDialogAi\Core\Graph\Edge\DirectedEdge;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Tests\TestCase;

class DGraphTest extends TestCase
{
    use ArraySubsetAsserts;

    /* @var DGraphClient */
    private $dGraphClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->dGraphClient = $this->app->make(DGraphClient::class);
    }

    /**
     * @group local
     * @requires DGRAPH
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
                'id'
            ]);

        /* @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $this->assertEquals('testNode', $response->getData()[0]['id']);
    }

    /**
     * @group local
     * @requires DGRAPH
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
                'id',
                'name'
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
                $attribute = AttributeResolver::getAttributeFor($name, $value);
                $node1->addAttribute($attribute);
            } catch (AttributeIsNotSupported $e) {
                // Simply skip attributes we can't deal with.
                continue;
            }
        }

        $this->assertTrue($node1->hasAttribute('name'));
        $node1->setAttribute('name', 'Mario Rossi');

        $mutation = new DGraphMutation($node1);
        $this->dGraphClient->tripleMutation($mutation);

        // Now retrieve the node using the uid and check that the name is Mario Rossi
        $query = new DGraphQuery();
        $query->uid($uid)
            ->setQueryGraph([
                'uid',
                'id',
                'name'
            ]);

        /* @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $this->assertTrue($response->getData()[0]['name'] == 'Mario Rossi');
    }

    /**
     * @requires DGRAPH
     */
    public function testMutationWithManyIntentsWithSameId()
    {
        /** @var Conversation $conversationModel */
        $conversationModel = Conversation::create([
            'name' => 'rock_paper_scissors',
            'model' => $this->getMarkupForManyIntentConversation()
        ]);

        $conversation = $conversationModel->buildConversation();

        $mutation = new DGraphMutation($conversation);

        $mutationString = $mutation->prepareTripleMutation();

        // Ensure the correct number of incoming intent relationships were specified
        $this->assertEquals(4, preg_match_all('/_:user_participant_in_opening_scene <says>/', $mutationString));
        $this->assertEquals(1, preg_match_all('/_:user_participant_in_opening_scene <says_across_scenes>/', $mutationString));

        // Ensure the correct number of `intent.app.send_choice` intents were defined
        $this->assertEquals(4, preg_match_all("/<id> \"intent\.app\.send_choice\"/", $mutationString));
    }

    /**
     * @requires DGRAPH
     */
    public function testSetAndGetFacets()
    {
        $client = resolve(DGraphClient::class);

        $node1 = new Node('node1');
        $node2 = new Node('node2');

        $facets = [
            'assignee' => 'user1',
            'count' => 2,
        ];

        $edge = new DirectedEdge('edge_with_facets', $node1, $node2, $facets);
        $node1->addOutgoingEdge($edge);

        $client->tripleMutation(new DGraphMutation($node1));

        $query = (new DGraphQuery())
            ->eq('id', 'node1')
            ->setQueryGraph([
                'id',
                'edge_with_facets' => [
                    DGraphQuery::WITH_FACETS,
                    'id',
                ],
            ]);

        $response = $client->query($query);
        $this->assertNotNull($response->getData());
        $this->assertNotEmpty($response->getData());

        $this->assertArraySubset([[
            'id' => 'node1',
            'edge_with_facets' => [[
                'id' => 'node2',
            ]],
            'edge_with_facets|count' => [
                2,
            ],
            'edge_with_facets|assignee' => [
                'user1',
            ],
        ]], $response->getData());
    }
}
