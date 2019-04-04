<?php


namespace OpenDialogAi\Core\Graph\Tests;


use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Unit\Conversation\ConversationTest;

class DGraphTest extends TestCase
{
    const DGRAPH_URL = 'http://10.0.2.2';
    const DGRAPH_PORT = '8080';

    private $dGraphClient;


    public function setUp(): void
    {
        parent::setUp();
        $this->dGraphClient = new DGraphClient(self::DGRAPH_URL, self::DGRAPH_PORT);
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
        $mutationResponse = $this->dGraph->tripleMutation($mutation);
        $this->assertEquals('Success', $mutationResponse->getData()['code']);

        // Now attempts to retrieve that node

        $query = new DGraphQuery();
        $query->eq('id', 'testNode')
            ->setQueryGraph([
                'uid',
                'id'
            ]);

        /* @var DGraphQueryResponse $reponse */
        $response = $this->dGraphClient->query($query);
        dd($response);
    }

    /**
     * @group local
     */
    public function testDGraphMutation()
    {
    }


}