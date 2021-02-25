<?php

namespace OpenDialogAi\GraphQLClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static updateSchema(string $schema)
 * @method static query(string $endpoint, string $query, string $variables)
 */
class GraphQLClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\GraphQLClient\GraphQLClient::class;
    }
}

