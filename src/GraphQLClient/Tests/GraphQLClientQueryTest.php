<?php


namespace OpenDialogAi\GraphQLClient\Tests;


use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClient;
use OpenDialogAi\GraphQLClient\GraphQLClientQueryErrorException;
use OpenDialogAi\GraphQLClient\GraphQLClientServiceProvider;

class GraphQLClientQueryTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = resolve(GraphQLClient::class);
        $this->client->dropAll();
        $this->client->updateSchema(file_get_contents(__DIR__ . '/../schema/schema.gql'));
    }
    public function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class
        ];
    }

    public function testDropAll()
    {
        $response = $this->client->dropAll();
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(["code" => "Success", "message" => "Done"], $response['data']);
    }

    public function testInvalidQueryFormat()
    {
        $query = <<<'GQL'
        query {
            queryScenario {
                id
        }
GQL;
        $this->expectException(GraphQLClientQueryErrorException::class);
        $this->client->query(GraphQLClient::QUERY_ENDPOINT, $query, []);
    }

    public function testInvalidQueryField()
    {
        $query = <<<'GQL'
        query {
            invalidField {
                id
            }
        }
GQL;
        $this->expectException(GraphQLClientQueryErrorException::class);
        $this->client->query(GraphQLClient::QUERY_ENDPOINT, $query, []);

    }

    public function testGetSchema()
    {
        $client = resolve(GraphQLClient::class);
        $query = <<<'GQL'
        {
          __schema {
            types {
              name
            }
          }
        }
GQL;

        $response = $this->client->query(GraphQLClient::QUERY_ENDPOINT, $query);

        return $response;
    }

    public function testSetSchema()
    {

        $schema = <<<'GQL'
        type Test {
            id: ID!
            name: String!
        }
GQL;

        $client = resolve(GraphQLClient::class);

        $response = $this->client->updateSchema($schema);

        $this->assertArrayNotHasKey("errors", $response);
        $this->assertArrayHasKey("data", $response);
        $this->assertEquals($schema, $response['data']['updateGQLSchema']['gqlSchema']['schema']);

    }

    public function testMutationAndQuery()
    {

        $mutation = <<<'GQL'
        mutation {
            addScenario(input: {od_id: "test_scenario", active: false, status: DRAFT, name: "Test Scenario" }) {
                scenario {
                    id
                }
            }
        }
GQL;


        $response = $this->client->query(GraphQLClient::QUERY_ENDPOINT, $mutation, []);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertIsString($response['data']['addScenario']['scenario'][0]["id"]);
        $newScenarioId = $response['data']['addScenario']['scenario'][0]['id'];

        $query = <<<'GQL'
        query {
          queryScenario {
            id
          }
        }
        GQL;

        $response = $this->client->query(GraphQLClient::QUERY_ENDPOINT, $query, []);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertIsArray($response['data']['queryScenario']);
        $this->assertEquals(1, count($response['data']['queryScenario']));
        $this->assertEquals($newScenarioId, $response['data']['queryScenario'][0]['id']);
        return $response;
    }

}
