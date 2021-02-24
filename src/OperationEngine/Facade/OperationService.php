<?php
namespace OpenDialogAi\OperationEngine\Facade;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\OperationEngine\OperationInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

/**
 * @method static array getAvailableOperations()
 * @method static bool isOperationAvailable(string $operationName)
 * @method static OperationInterface getOperation($operationName)
 * @method static void registerAvailableOperations($operations)
 * @method static bool checkCondition(Condition $condition)
 */
class OperationService extends Facade
{
    /**
     * @method static function checkCondition(Condition $condition)
     **/
    protected static function getFacadeAccessor()
    {
        return OperationServiceInterface::class;
    }
}
