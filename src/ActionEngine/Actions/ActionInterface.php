<?php

namespace OpenDialogAi\ActionEngine\Actions;

use ActionEngine\Exceptions\ActionNameNotSetException;
use OpenDialogAi\ActionEngine\Results\ActionResult;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * This is a placeholder interface for what an action needs to de
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
     * Performs the action with the given name if it exists
     *
     * @param string $action The name of the action to perform
     * @return ActionResult
     */
    public function perform(string $action) : ActionResult;

    /**
     * Returns an array of attribute names that the action requires in order to be performed
     *
     * @return string[]
     */
    public function requiresAttributes() : array;

    /**
     * Checks whether the action requires the specified attribute
     *
     * @param $attributeName string The name of the attribute to check
     * @return bool True if the action requires this attribute, false if not
     */
    public function requiresAttribute($attributeName) : bool;

    /**
     * @param string $attributeName The name of attribute to fill
     * @param AttributeInterface $attributeValue The resolved value of the attribute
     * @return mixed
     */
    public function setAttributeValue($attributeName, AttributeInterface $attributeValue);
}
