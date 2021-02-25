<?php

namespace OpenDialogAi\GraphQLClient;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;


class GraphQLClient
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

    public function dropAll(): array
    {
        $response = $this->_jsonRequest("/alter", ["drop_all" => true]);

        $json = self::__decodeJsonResponse($response);

        if (isset($json['errors'])) {
            throw new GraphQLClientQueryErrorException("GraphQL response body contains errors.", $json['errors']);
        }
        return $json;
    }

    public function updateSchema(string $schema): array
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
        return $this->query("/admin", $updateSchema, ["schema" => $schema]);
    }


    static function createRequestJson(string $query, array $variables = []): array
    {
        $json = [
            "query" => $query
        ];
        if (!empty($variables)) {
            $json["variables"] = $variables;
        }
        return $json;
    }

    private function _jsonRequest(string $endpoint, array $json): ResponseInterface
    {
        return $this->httpClient->post($endpoint, [
            "headers" => ["content-type" => "application/json"], "json" => $json
        ]);
    }

    private static function __decodeJsonResponse($response): array
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

    public function query(string $endpoint, string $query, array $variables = []): array
    {
        $response = $this->_jsonRequest($endpoint, self::createRequestJson($query, $variables));
        $json = self::__decodeJsonResponse($response);

        if (isset($json['errors'])) {
            throw new GraphQLClientQueryErrorException("GraphQL response body contains errors.", $json['errors']);
        }
        return $json;
    }

}
