<?php

return [
    'PACKAGE_NAME' => env('PACKAGE_NAME'),
    'DGRAPH_URL' => env('DGRAPH_URL', 'http://10.0.2.2'),
    'DGRAPH_PORT' => env('DGRAPH_PORT', '8080'),
    'DGRAPH_AUTH_TOKEN' => env('DGRAPH_AUTH_TOKEN', null),

    'LOG_DGRAPH_QUERIES' => env("LOG_DGRAPH_QUERIES", false),

    'LOG_API_CALLS' => env('LOG_API_CALLS', true)
];
