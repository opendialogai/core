<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\ODObjectCollection;
use OpenDialogAi\OperationEngine\Facade\OperationService;

class ConditionFilter
{
    /**
     * @param ODObjectCollection $objects
     * @return ODObjectCollection
     */
    public static function filterObjects(ODObjectCollection $objects): ODObjectCollection
    {
        return $objects;
    }

    /**
     * Returns whether the conditions for the given object all passed
     *
     * @param ConversationObject $object
     * @return bool
     */
    public static function checkConditionsForObject(ConversationObject $object): bool
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

    /**
     * Returns whether the conditions all passed
     *
     * @param ConditionCollection $conditions
     * @return bool
     */
    public static function checkConditions(ConditionCollection $conditions): bool
    {
        return false;
    }

    /**
     * Returns whether the condition passed
     *
     * @param Condition $condition
     * @return bool
     */
    public static function checkCondition(Condition $condition): bool
    {
        return false;
    }
}
