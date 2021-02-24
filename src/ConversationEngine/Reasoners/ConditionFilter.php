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
        return $objects->filter(function (ConversationObject $object) {
            return self::checkConditionsForObject($object);
        });
    }

    /**
     * Returns whether the conditions for the given object all passed
     *
     * @param ConversationObject $object
     * @return bool
     */
    public static function checkConditionsForObject(ConversationObject $object): bool
    {
        return self::checkConditions($object->getConditions());
    }

    /**
     * Returns whether the conditions all passed
     *
     * @param ConditionCollection $conditions
     * @return bool
     */
    public static function checkConditions(ConditionCollection $conditions): bool
    {
        $allConditionsPassed = true;

        $conditions->each(function (Condition $condition) use (&$allConditionsPassed) {
            $conditionPassed = self::checkCondition($condition);

            if (!$conditionPassed) {
                $allConditionsPassed = false;
            }

            // Will break when this returns false
            return $conditionPassed;
        });

        return $allConditionsPassed;
    }

    /**
     * Returns whether the condition passed
     *
     * @param Condition $condition
     * @return bool
     */
    public static function checkCondition(Condition $condition): bool
    {
        return OperationService::checkCondition($condition);
    }
}
