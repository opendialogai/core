<?php

namespace OpenDialogAi\ActionEngine\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;

/**
 * @method static void setAvailableActions($supportedActions)
 * @method static void unSetAvailableActions()
 * @method static array getAvailableActions()
 * @method static ActionResult performAction(string $actionName, Collection $inputAttributes):
 * @method static void registerAction(ActionInterface $action)
*/
class ActionService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionEngineInterface::class;
    }
}
