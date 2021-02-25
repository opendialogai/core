<?php

namespace OpenDialogAi\GraphQLClient\Tests;

use GuzzleHttp\Exception\TransferException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClient;
use OpenDialogAi\GraphQLClient\GraphQLClientQueryErrorException;
use OpenDialogAi\GraphQLClient\GraphQLClientServiceProvider;

class GraphQLClientTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class
        ];
    }

    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }

    public function schemaQuery() {
        return <<<'GQL'
        {
          __schema {
            types {
              name
            }
          }
        }
GQL;
    }


    public function testCreateGraphQLClient() {

        $client = resolve(GraphQLClient::class);
        $this->assertInstanceOf(GraphQLClient::class, $client);
        $responseJson = $client->query(GraphQLClient::QUERY_ENDPOINT, $this->schemaQuery());
        $this->assertArrayHasKey('data', $responseJson);
        $this->assertArrayNotHasKey('errors', $responseJson);
    }

    public function testIncorrectURL() {
        $this->setConfigValue('opendialog.core.DGRAPH_URL', 'dgraph-server.invalid');
        $client = resolve(GraphQLClient::class);
        $this->expectException(TransferException::class);
        $client->query(GraphQLClient::QUERY_ENDPOINT, $this->schemaQuery());
    }

    public function testIncorrectPort() {
        $this->setConfigValue('opendialog.core.DGRAPH_PORT', '47');
        $client = resolve(GraphQLClient::class);
        $this->expectException(TransferException::class);
        $client->query(GraphQLClient::QUERY_ENDPOINT, $this->schemaQuery());

    }

    public function testIncorrectAuthToken() {
        $this->setConfigValue('opendialog.core.DGRAPH_AUTH_TOKEN', 'invalidauthtoken');
        $client = resolve(GraphQLClient::class);

        $testSchema = <<<'GQL'
        type Test {
            id: ID!
            test: String!
        }
GQL;

        $this->expectException(GraphQLClientQueryErrorException::class);
        $response = $client->updateSchema($testSchema);
        $this->assertArrayHasKey("errors", $response);
        $this->assertArrayNotHasKey("data", $response);
    }

}
