<?php

namespace OpenDialogAi\ActionEngine\Actions;

use Ds\Map;

/**
 * An action takes an input of attributes, performs its action and returns a response containing details of the action
 * results.
 */
interface ActionInterface
{
    /**
     * Performs the action
     *
     * @param ActionInput $actionInput
     * @return ActionResult
     */
    public function perform(ActionInput $actionInput): ActionResult;

    /**
     * Returns an array of attribute names that the action requires in order to be performed
     *
     * @return string[]
     */
    public function getRequiredAttributes(): array;

    /**
     * Returns an array of attribute names that the action give as input
     *
     * @return Map
     */
    public function getInputAttributes(): Map;

    /**
     * Checks whether the action requires the specified attribute
     *
     * @param $attributeName string The name of the attribute to check
     * @return bool True if the action requires this attribute, false if not
     */
    public function requiresAttribute($attributeName): bool;

    /**
     * Returns an array of attribute names that the action will output if successful
     *
     * @return Map
     */
    public function getOutputAttributes(): Map;

    /**
     * Whether this action outputs the given attribute
     *
     * @param $attributeName
     * @return bool
     */
    public function outputsAttribute($attributeName): bool;
}
