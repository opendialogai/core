<?php

namespace OpenDialogAi\Core\Graph\Tests\DGraph;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Tests\TestCase;

class DGraphSchemaTest extends TestCase
{
    public function getClient(bool $withCustomSchema = true): DGraphClient
    {
        $dGraphClient = $this->app->make(DGraphClient::class);

        if ($withCustomSchema) {
                $dGraphClient->setSchema($dGraphClient->getSchema() . "
                    <tag_name>: string @index(exact) .
                    <has_tag>: [uid] @reverse .
                    
                    type Tag {
                        tag_name: string
                    }
                ");
        }

        $dGraphClient->initSchema();
        return $dGraphClient;
    }

    /**
     * @group local
     * @requires DGRAPH
     */
    public function testQueryingCustomNodeWithCustomSchema()
    {
        $client = $this->getClient();
        $this->addTagToGraph($client);
        $response = $this->runTagQuery($client);
        $this->assertNotNull($response->getData());

        $this->assertCount(1, $response->getData());
        $this->assertArrayHasKey('tag_name', $response->getData()[0]);
        $this->assertEquals('Tag1', $response->getData()[0]['tag_name']);
        $this->assertArrayHasKey('~has_tag', $response->getData()[0]);
        $this->assertCount(1, $response->getData()[0]['~has_tag']);
        $this->assertArrayHasKey('id', $response->getData()[0]['~has_tag'][0]);
        $this->assertEquals('test_node', $response->getData()[0]['~has_tag'][0]['id']);
    }

    /**
     * @group local
     * @requires DGRAPH
     */
    public function testQueryingCustomNodeWithoutCustomSchema()
    {
        $client = $this->getClient(false);
        $this->addTagToGraph($client);
        $response = $this->runTagQuery($client);
        $this->assertNull($response->getData());
    }

    /**
     * @param DGraphClient $client
     * @return void
     */
    public function addTagToGraph(DGraphClient $client): void
    {
        $node = new Node('test_node');

        $tagNode = new Node('my_tag');
        $tagNode->setGraphType('Tag');
        $tagNode->addAttribute(new StringAttribute('tag_name', 'Tag1'));

        $node->createOutgoingEdge('has_tag', $tagNode);

        $mutation = new DGraphMutation($node);

        $mutationResponse = $client->tripleMutation($mutation);
        $this->assertEquals('Success', $mutationResponse->getData()['code']);
    }

    /**
     * @param DGraphClient $client
     * @return DGraphQueryResponse
     */
    public function runTagQuery(DGraphClient $client): DGraphQueryResponse
    {
        $query = new DGraphQuery();
        $query->eq('tag_name', 'Tag1')
            ->setQueryGraph([
                'uid',
                'tag_name',
                '~has_tag' => [
                    'uid',
                    'id'
                ]
            ]);

        return $client->query($query);
    }
}
