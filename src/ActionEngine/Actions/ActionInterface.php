<?php

namespace OpenDialogAi\ActionEngine\Actions;

use ActionEngine\Exceptions\ActionNameNotSetException;
use ActionEngine\Input\ActionInput;
use OpenDialogAi\ActionEngine\Output\ActionResult;

/**
 * An action takes an input of attributes, performs its action and returns a response containing details of the action
 * results.
 */
interface ActionInterface
{
    /**
     * Gets the name of the action that can be performed
     *
     * @return string
     * @throws ActionNameNotSetException
     */
    public function performs() : string;

    /**
     * Performs the action
     *
     * @param ActionInput $actionInput
     * @return ActionResult
     */
    public function perform(ActionInput $actionInput) : ActionResult;

    /**
     * Returns an array of attribute names that the action requires in order to be performed
     *
     * @return string[]
     */
    public function getRequiredAttributes() : array;

    /**
     * Checks whether the action requires the specified attribute
     *
     * @param $attributeName string The name of the attribute to check
     * @return bool True if the action requires this attribute, false if not
     */
    public function requiresAttribute($attributeName) : bool;

    /**
     * Returns an array of attribute names that the action will output if successful
     *
     * @return array
     */
    public function getOutputAttributes() : array;

    /**
     * Whether this action outputs the given attribute
     *
     * @param $attributeName
     * @return bool
     */
    public function outputsAttribute($attributeName): bool;
}
