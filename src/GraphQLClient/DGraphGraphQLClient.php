<?php

namespace OpenDialogAi\GraphQLClient;

use GuzzleHttp\Client;
use OpenDialogAi\GraphQLClient\Exceptions\GraphQLClientErrorResponseException;
use OpenDialogAi\GraphQLClient\Exceptions\GraphQLClientException;
use Psr\Http\Message\ResponseInterface;


class DGraphGraphQLClient implements GraphQLClientInterface
{
    const QUERY_ENDPOINT = "/graphql";
    const ADMIN_ENDPOINT = "/admin";
    const ALTER_ENDPOINT = "/alter";

    protected Client $httpClient;

    public function __construct(string $url, string $port, array $headers)
    {
        $this->httpClient = new Client([
            'base_uri' => $url.":".$port, 'headers' => $headers,
        ]);
    }


    /**
     * Drops all data from DGraph. The schema is preserved
     *
     * @throws GraphQLClientErrorResponseException
     * @throws GraphQLClientException
     */
    public function dropData()
    {
        $response = $this->_jsonRequest(self::ALTER_ENDPOINT, ["drop_op" => "DATA"]);
        $json = self::__decodeJsonResponse($response);

        if (isset($json['errors'])) {
            throw new GraphQLClientErrorResponseException("GraphQL response body contains errors.", $json['errors']);
        }
    }


    /**
     * Drop all data and schema from DGraph
     *
     * @return void
     * @throws GraphQLClientErrorResponseException
     * @throws GraphQLClientException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function dropAll()
    {
        $response = $this->_jsonRequest(self::ALTER_ENDPOINT, ["drop_all" => true]);
        $json = self::__decodeJsonResponse($response);

        if (isset($json['errors'])) {
            throw new GraphQLClientErrorResponseException("GraphQL response body contains errors.", $json['errors']);
        }
    }

    /**
     * Make a JSON request using the HTTP Client
     *
     * @param  string  $endpoint
     * @param  array   $json
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _jsonRequest(string $endpoint, array $json): ResponseInterface
    {
        return $this->httpClient->post($endpoint, [
            "headers" => ["content-type" => "application/json"], "json" => $json
        ]);
    }


    /**
     * Decode a response body as JSON
     *
     * @param $response
     *
     * @return array
     * @throws GraphQLClientException
     */
    private static function __decodeJsonResponse(ResponseInterface $response): array
    {
        if (!$response->getBody()) {
            throw new GraphQLClientException("Empty response from GraphQL server.");
        }

        try {
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new GraphQLClientException("Error decoding json response from GraphQL server.", 0, $exception);
        }
    }


    /**
     * Set the DGraph GraphQL schema.
     * The schema string should contain type definitions only.
     * Queries, Mutations and other objects with be generated automatically by DGraph.
     *
     * @param  string  $schema
     *
     * @return array
     * @throws GraphQLClientErrorResponseException
     */
    public function setSchema(string $schema)
    {
        $updateSchema = <<<'GQL'
            mutation updateSchema($schema: String!) {
                updateGQLSchema(input: { set: { schema: $schema } }) {
                    gqlSchema {
                      schema
                      generatedSchema
                    }
                }
            }
        GQL;
        return $this->request(self::ADMIN_ENDPOINT, $updateSchema, ["schema" => $schema]);
    }

    /**
     * Make a GraphQL Query to a specified endpoint
     *
     * @param  string  $endpoint
     * @param  string  $query
     * @param  array   $variables
     *
     * @return array
     * @throws GraphQLClientErrorResponseException
     * @throws GraphQLClientException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $endpoint, string $query, array $variables = []): array
    {
        $response = $this->_jsonRequest($endpoint, self::createGraphQLRequestJson($query, $variables));
        $json = self::__decodeJsonResponse($response);

        if (isset($json['errors'])) {
            throw new GraphQLClientErrorResponseException("GraphQL response body contains errors.", $json['errors']);
        }
        return $json;
    }

    /**
     * Creates JSON for a GraphQL POST request from query string and variables map.
     *
     * @param  string  $query
     * @param  array   $variables
     *
     * @return string[]
     */
    static private function createGraphQLRequestJson(string $query, array $variables = []): array
    {
        $json = [
            "query" => $query
        ];
        if (!empty($variables)) {
            $json["variables"] = $variables;
        }
        return $json;
    }

    /**
     * Make a query to the DGraph /graphql query endpoint
     *
     * @param  string  $query
     * @param  array   $variables
     *
     * @return array
     * @throws GraphQLClientErrorResponseException
     * @throws GraphQLClientException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query(string $query, array $variables = []): array
    {
        return $this->request(self::QUERY_ENDPOINT, $query, $variables);
    }

}
