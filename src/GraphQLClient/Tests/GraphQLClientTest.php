<?php

namespace OpenDialogAi\GraphQLClient\Tests;

use GuzzleHttp\Exception\TransferException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\DGraphGraphQLClient;
use OpenDialogAi\GraphQLClient\Exceptions\GraphQLClientErrorResponseException;
use OpenDialogAi\GraphQLClient\GraphQLClientServiceProvider;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class GraphQLClientTest extends TestCase
{

    public function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class
        ];
    }

    public function testCreateGraphQLClient()
    {
        $client = resolve(GraphQLClientInterface::class);
        $this->assertInstanceOf(DGraphGraphQLClient::class, $client);
        $responseJson = $client->query($this->schemaQuery());
        $this->assertArrayHasKey('data', $responseJson);
        $this->assertArrayNotHasKey('errors', $responseJson);
    }

    public function schemaQuery()
    {
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

    public function testIncorrectURL()
    {
        $this->setConfigValue('opendialog.core.DGRAPH_URL', 'dgraph-server.invalid');
        $client = resolve(GraphQLClientInterface::class);
        $this->expectException(TransferException::class);
        $client->query($this->schemaQuery());
    }

    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }

    public function testIncorrectPort()
    {
        $this->setConfigValue('opendialog.core.DGRAPH_PORT', '47');
        $client = resolve(GraphQLClientInterface::class);
        $this->expectException(TransferException::class);
        $client->query($this->schemaQuery());

    }

    public function testIncorrectAuthToken()
    {
        $this->setConfigValue('opendialog.core.DGRAPH_AUTH_TOKEN', 'invalidauthtoken');
        $client = resolve(GraphQLClientInterface::class);

        $testSchema = <<<'GQL'
        type Test {
            id: ID!
            test: String!
        }
GQL;

        $this->expectException(GraphQLClientErrorResponseException::class);
        $response = $client->setSchema($testSchema);
        $this->assertArrayHasKey("errors", $response);
        $this->assertArrayNotHasKey("data", $response);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

}
