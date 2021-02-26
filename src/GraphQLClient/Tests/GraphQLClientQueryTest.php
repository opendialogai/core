<?php


namespace OpenDialogAi\GraphQLClient\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\Exceptions\GraphQLClientErrorResponseException;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use OpenDialogAi\GraphQLClient\GraphQLClientServiceProvider;

class GraphQLClientQueryTest extends TestCase
{

    public function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class
        ];
    }

    public function testInvalidQueryFormat()
    {
        $query = <<<'GQL'
            query {
                A {
                    id
            }
        GQL;
        $this->expectException(GraphQLClientErrorResponseException::class);
        $this->client->query($query, []);
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
        $this->expectException(GraphQLClientErrorResponseException::class);
        $this->client->query($query, []);

    }

    public function testGetSchema()
    {
        $query = <<<'GQL'
            {
              __schema {
                types {
                  name
                }
              }
            }
        GQL;

        $response = $this->client->query($query);
        $this->assertArrayHasKey("data", $response);
        $this->assertIsArray($response['data']['__schema']['types']);
    }

    public function testSetSchema()
    {

        $schema = <<<'GQL'
            type Test {
                id: ID!
                name: String!
            }
        GQL;
        $response = $this->client->setSchema($schema);

        $this->assertArrayNotHasKey("errors", $response);
        $this->assertArrayHasKey("data", $response);
        $this->assertEquals($schema, $response['data']['updateGQLSchema']['gqlSchema']['schema']);

    }

    public function testMutationAndQuery()
    {
        $mutation = <<<'GQL'
            mutation {
                addA(input: {name: "Test A"}) {
                    a {
                        id
                    }
                }
            }
        GQL;


        $response = $this->client->query($mutation, []);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertIsString($response['data']['addA']['a'][0]["id"]);
        $newId = $response['data']['addA']['a'][0]['id'];

        $query = <<<'GQL'
            query {
              queryA {
                id
              }
            }
        GQL;

        $response = $this->client->query($query, []);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertIsArray($response['data']['queryA']);
        $this->assertEquals(1, count($response['data']['queryA']));
        $this->assertEquals($newId, $response['data']['queryA'][0]['id']);
        return $response;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = resolve(GraphQLClientInterface::class);
        $this->client->dropAll();
        $testSchema = <<<'GQL'
            type A {
                id: ID!
                name: String!
                bs: [B] @hasInverse(field:"a")
            }

            type B {
                id: ID!
                name: String!
                a: A
            }
        GQL;


        $this->client->setSchema($testSchema);
    }

}
