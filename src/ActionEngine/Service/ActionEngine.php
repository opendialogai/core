<?php

namespace OpenDialogAi\ActionEngine\Service;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Components\InvalidComponentDataException;
use OpenDialogAi\Core\Components\MissingRequiredComponentDataException;
use OpenDialogAi\Core\Exceptions\NameNotSetException;

class ActionEngine implements ActionEngineInterface
{
    /** @var ActionInterface[] */
    private $availableActions = [];

    /**
     * @inheritdoc
     */
    public function setAvailableActions($supportedActions): void
    {
        foreach ($supportedActions as $supportedAction) {
            try {
                if (!class_exists($supportedAction) || !in_array(ActionInterface::class, class_implements($supportedAction))) {
                    Log::warning(
                        sprintf(
                            "Skipping adding action %s to list of supported actions as it does not exist or is wrong type",
                            $supportedAction
                        )
                    );

                    break;
                }

                /** @var ActionInterface $action */
                $action = new $supportedAction();
                $action::getComponentData();
                $this->registerAction($action);
            } catch (NameNotSetException $exception) {
                Log::warning(
                    sprintf(
                        "Skipping adding action %s to list of supported actions as it doesn't have a name",
                        $supportedAction
                    )
                );
            } catch (MissingRequiredComponentDataException $e) {
                Log::warning(
                    sprintf(
                        "Skipping adding action %s to list of supported actions as it doesn't have a %s",
                        $supportedAction,
                        $e->data
                    )
                );
            } catch (InvalidComponentDataException $e) {
                Log::warning(
                    sprintf(
                        "Skipping adding action %s to list of supported actions as its given %s ('%s') is invalid",
                        $supportedAction,
                        $e->data,
                        $e->value
                    )
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAvailableActions(): array
    {
        return $this->availableActions;
    }

    /**
     * Helper function to allow you to ignore any predefined config.
     */
    public function unsetAvailableActions(): void
    {
        $this->availableActions = [];
    }

    /**
     * Will not run the action if the input attributes do not match the required attributes.
     * @inheritdoc
     */
    public function performAction(string $actionName, Map $inputAttributes): ActionResult
    {
        if ($this->actionIsAvailable($actionName)) {
            $action = $this->availableActions[$actionName];
            $action->setInputAttributes($inputAttributes);

            $inputAttributes = $action->getInputAttributes();

            $actionInput = $this->getActionInput($inputAttributes);

            if (!$actionInput->containsAllAttributes($action->getRequiredAttributes())) {
                Log::warning(
                    sprintf(
                        "Skipping action %s because some required attributes does not exist",
                        $actionName
                    )
                );

                return ActionResult::createFailedActionResult();
            }

            return $action->perform($actionInput);
        }

        Log::warning(sprintf(
            "Action %s is not available and cannot be performed",
            $actionName
        ));

        return ActionResult::createFailedActionResult();
    }

    /**
     * Check if the action with given name is available
     *
     * @param $actionName
     * @return bool
     */
    private function actionIsAvailable($actionName)
    {
        return isset($this->availableActions[$actionName]);
    }

    /**
     * Loops through all input attributes, tries to get each attribute from the given context and adds to the action input.
     * If the specified attribute does not exist, it is not added to the action input
     * @param Map $inputAttributes
     * @return ActionInput
     */
    private function getActionInput(Map $inputAttributes): ActionInput
    {
        $actionInput = new ActionInput();
        foreach ($inputAttributes as $attributeId => $contextId) {
            try {
                $attribute = ContextService::getAttribute($attributeId, $contextId);
                $actionInput->addAttribute($attribute);
                Log::debug(sprintf('Adding attribute %s to action input', $attributeId));
            } catch (AttributeDoesNotExistException $e) {
                Log::warning(
                    sprintf(
                        'Unable to add attribute %s to action input, it does not exist in context %s',
                        $attributeId,
                        $contextId
                    )
                );
            }
        }
        return $actionInput;
    }

    /**
     * Registers an action to the engine. This method is useful for mocking actions in tests.
     *
     * @param ActionInterface $action
     * @throws NameNotSetException
     */
    public function registerAction(ActionInterface $action): void
    {
        $this->availableActions[$action::getComponentId()] = $action;
    }
}
