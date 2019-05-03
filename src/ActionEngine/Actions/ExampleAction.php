<?php


namespace OpenDialogAi\ActionEngine\Actions;


use OpenDialogAi\Core\Attribute\StringAttribute;

/**
 * This is an example action to demonstrate how actions can be implemented for OpenDialog.
 */
class ExampleAction extends BaseAction
{
    protected $performs = 'action.core.example';

    public function __construct()
    {
        $this->requiredAttributes = ['user.first_name', 'user.last_name'];

        $this->outputsAttributes = ['full_name'];
    }

    /**
     * @param ActionInput $actionInput
     * @return ActionResult
     */
    public function perform(ActionInput $actionInput): ActionResult
    {
        $fullName = $actionInput->getAttributeBag()->getAttribute('first_name')->getValue() .
            $actionInput->getAttributeBag()->getAttribute('last_name')->getValue();

        $fullNameAttribute = new StringAttribute('full_name', $fullName);

        $result = new ActionResult(true);
        $result->addAttribute($fullNameAttribute);
        return $result;
    }

}
