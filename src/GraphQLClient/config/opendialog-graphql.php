<?php

return [
    'DGRAPH_INSTANCE_TYPE' => env('DGRAPH_INSTANCE_TYPE', null),
    'DGRAPH_BASE_URL' => env('DGRAPH_BASE_URL', null),
    'DGRAPH_PORT' => env('DGRAPH_PORT', null),
    'DGRAPH_AUTH_TOKEN' => env('DGRAPH_AUTH_TOKEN', null),
    'SLASH_GRAPHQL_API_KEY' => env('SLASH_GRAPHQL_API_KEY', null),
    'schema' => file_get_contents(__DIR__ . "/input_schema.gql")
];
