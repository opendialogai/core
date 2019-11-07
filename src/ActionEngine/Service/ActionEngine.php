<?php

namespace OpenDialogAi\ActionEngine\Service;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNameNotSetException;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ContextEngine\Facades\ContextService;

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
                $this->availableActions[$action->performs()] = $action;
            } catch (ActionNameNotSetException $exception) {
                Log::warning(
                    sprintf(
                        "Skipping adding action %s to list of supported actions as it doesn't have a name",
                        $supportedAction
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
     * @inheritdoc
     */
    public function performAction(string $actionName, Map $inputAttributes): ?ActionResult
    {
        if ($this->actionIsAvailable($actionName)) {
            $action = $this->availableActions[$actionName];
            $action->setInputAttributes($inputAttributes);

            $requiredAttributes = $action->getRequiredAttributes();
            $inputAttributes = $action->getInputAttributes();

            if (!empty(array_diff($requiredAttributes, $inputAttributes->keys()->toArray()))) {
                Log::warning(
                    sprintf(
                        "Skipping action %s because some required attributes does not exist",
                        $actionName
                    )
                );

                return null;
            }

            // Get action input
            $actionInput = $this->getActionInput($requiredAttributes, $inputAttributes);

            return $action->perform($actionInput);
        }

        throw new ActionNotAvailableException(
            sprintf(
                "Action %s is not available and cannot be performed",
                $actionName
            )
        );
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
     * @param array $requiredAttributes
     * @param Map $inputAttributes
     * @return ActionInput
     */
    private function getActionInput(array $requiredAttributes, Map $inputAttributes): ActionInput
    {
        $actionInput = new ActionInput();
        foreach ($inputAttributes as $attributeId => $contextId) {
            $attribute = ContextService::getAttribute($attributeId, $contextId);
            $actionInput->addAttribute($attribute);
        }
        return $actionInput;
    }
}
