<?php

namespace OpenDialogAi\ActionEngine\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ActionEngine\ActionEngineServiceProvider;

/**
 * The action engine is bound to the service layer in @see ActionEngineServiceProvider
 *
 * @method static array getAvailableActions()
 */
class ActionEngine extends Facade
{
    const ACTION_ENGINE_SERVICE = 'action-engine-service';

    protected static function getFacadeAccessor()
    {
        return self::ACTION_ENGINE_SERVICE;
    }
}
