<?php


namespace OpenDialogAi\Core\Tests\Unit\Graph\DGraph;


use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Unit\Conversation\ConversationTest;

class DGraphTest extends TestCase
{
    const DGRAPH_URL = 'http://10.0.2.2';
    const DGRAPH_PORT = '8080';

    /**
     * @group onlylocal
     */
    public function testDGraphQuery()
    {
        $dGraph = new DGraphClient(self::DGRAPH_URL, self::DGRAPH_PORT);
        $this->assertTrue(true);

        $query = new DGraphQuery();
        $query->allofterms('ei_type', ['Participant'])
            ->setQueryGraph([
                'uid',
                'speaks' => [
                    'uid',
                    'speaks' => [
                        'uid',
                        'ei_type'
                    ]
                ],
            ]);

        $dGraph->query($query);
    }

    /**
     * @group onlylocal
     */
    public function testDGraphMutation()
    {
        $conversationData = new ConversationTest();
         /* @var ConversationManager $cm */
        $cm = $conversationData->setupConversation();

        $conversation = $cm->getConversation();

        $dGraph = new DGraphClient(self::DGRAPH_URL, self::DGRAPH_PORT);
        //$dGraph->dropSchema();

        $mutation = new DGraphMutation($conversation);
        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);
        $this->assertEquals('Success', $mutationResponse->getData()['code']);
    }


}