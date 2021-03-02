<?php


namespace OpenDialogAi\GraphQLClient\Tests;

use GuzzleHttp\Exception\ClientException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use OpenDialogAi\GraphQLClient\GraphQLClientServiceProvider;

class SlashGraphQLEndpointTest extends TestCase
{
    public function adminSchemaQuery(): string
    {
        return <<<'GQL'
        {
            getGQLSchema { schema }
        }
        GQL;
    }

    public function getPackageProviders($app)
    {
        return [
            GraphQLClientServiceProvider::class
        ];
    }

//    /**
//     * @group skip
//     */
//    public function testGetSchemaFromAdminEndpointWithInvalidAuth() {
//        //TODO: Reintroduce test. Need a custom slashGraphQL instance for testing.
//        $this->setConfigValue('opendialog.graphql.DGRAPH_INSTANCE_TYPE', 'SLASH_GRAPHQL');
//        $this->setConfigValue('opendialog.graphql.DGRAPH_BASE_URL', '<removed>');
//        $this->setConfigValue('opendialog.graphql.DGRAPH_PORT', 443);
//        $this->setConfigValue('opendialog.graphql.DGRAPH_AUTH_TOKEN', null);
//        $this->setConfigValue('opendialog.graphql.SLASH_GRAPHQL_API_KEY', 'invalid');
//        $client = resolve(GraphQLClientInterface::class);
//
//        $this->expectException(ClientException::class);
//        $client->request("/admin", $this->adminSchemaQuery());
//    }
//
//    /**
//     * @group skip
//     */
//    public function testGetSchemaFromAdminEndpoint() {
//        //TODO: Reintroduce test. Need a custom slashGraphQL instance for testing.
//        $this->setConfigValue('opendialog.graphql.DGRAPH_INSTANCE_TYPE', 'SLASH_GRAPHQL');
//        $this->setConfigValue('opendialog.graphql.DGRAPH_BASE_URL', '<removed>');
//        $this->setConfigValue('opendialog.graphql.DGRAPH_PORT', 443);
//        $this->setConfigValue('opendialog.graphql.DGRAPH_AUTH_TOKEN', null);
//        $this->setConfigValue('opendialog.graphql.SLASH_GRAPHQL_API_KEY', '<removed>');
//        $client = resolve(GraphQLClientInterface::class);
//
//        $response = $client->request("/admin", $this->adminSchemaQuery());
//        $this->assertIsArray($response);
//        $this->assertIsString($response['data']['getGQLSchema']['schema']);
//    }
}
