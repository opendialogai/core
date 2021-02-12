<?php
namespace OpenDialogAi\OperationEngine\Facade;

use Illuminate\Support\Facades\Facade;


class OperationService extends Facade
{
    /**
     * @method static function checkCondition(Condition $condition)
     **/
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\OperationEngine\Service\OperationServiceInterface::class;
    }
}
