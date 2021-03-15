<?php

namespace OpenDialogAi\GraphQLClient\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\GraphQLClient\DGraphGraphQLClient;

/**
 * @method static setSchema(string $schema)
 * @method static dropData()
 * @method static dropAll()
 * @method static query(string $query, string $variables)
 */
class GraphQLClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DGraphGraphQLClient::class;
    }
}

