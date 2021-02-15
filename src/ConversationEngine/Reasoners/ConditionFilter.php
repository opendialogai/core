<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\OperationEngine\Facade\OperationService;

class ConditionFilter
{
    public static function checkConditions(ConversationObject $object): bool
    {
        $conditions = $object->getConditions();
        // Check each condition in turn to see if it passes
        $passingConditions = $conditions->filter(function ($condition) {
            if (OperationService::checkCondition($condition)) {
                return true;
            }
            return false;
        });
        //If all the conditions passed we return true
        if (count($passingConditions) == count ($conditions)) {
            return true;
        }

        return false;
    }
}
