<?php

namespace OpenDialogAi\ResponseEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ResponseEngine\ResponseEngineServiceProvider;

/**
 * The action engine is bound to the service layer in @see ResponseEngineServiceProvider
 */
class ResponseEngine extends Facade
{
    const RESPONSE_ENGINE_SERVICE = 'response-engine-service';

    protected static function getFacadeAccessor()
    {
        return self::RESPONSE_ENGINE_SERVICE;
    }
}
