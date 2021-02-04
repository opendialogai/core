<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;

/**
 * This is an example action to demonstrate how actions can be implemented for OpenDialog.
 */
class ExampleAction extends BaseAction
{
    protected static $name = 'action.core.example';

    protected static ?string $componentName = 'Example action';
    protected static ?string $componentDescription = 'Just an example action.';

    protected static $requiredAttributes = ['first_name', 'last_name'];
    protected static $outputAttributes = ['first_name', 'last_name', 'full_name'];

    /**
     * @param ActionInput $actionInput
     * @return ActionResult
     */
    public function perform(ActionInput $actionInput): ActionResult
    {
        $firstName = $actionInput->getAttributeBag()->getAttribute('first_name')->getValue();
        $lastName = $actionInput->getAttributeBag()->getAttribute('last_name')->getValue();

        $fullName = $firstName . $lastName;

        $firstNameAttribute = new StringAttribute('first_name', $firstName);
        $lastNameAttribute = new StringAttribute('last_name', $lastName);
        $fullNameAttribute = new StringAttribute('full_name', $fullName);

        $result = new ActionResult(true);
        $result->addAttribute($firstNameAttribute);
        $result->addAttribute($lastNameAttribute);
        $result->addAttribute($fullNameAttribute);

        return $result;
    }
}
