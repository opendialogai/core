<?php

namespace OpenDialogAi\ActionEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNameNotSetException;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextParser;

class ActionEngine implements ActionEngineInterface
{
    /** @var AttributeResolver */
    private $attributeResolver;

    /** @var ContextService */
    private $contextService;

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
     * @param AttributeResolver $attributeResolver
     */
    public function setAttributeResolver(AttributeResolver $attributeResolver)
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * @param ContextService $contextService
     */
    public function setContextService(ContextService $contextService)
    {
        $this->contextService = $contextService;
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
    public function performAction(string $actionName): ActionResult
    {
        if ($this->actionIsAvailable($actionName)) {
            $action = $this->availableActions[$actionName];

            // Get action input
            $actionInput = $this->getActionInput($action->getRequiredAttributes());
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
     * @return ActionInput
     */
    private function getActionInput(array $requiredAttributes): ActionInput
    {
        $actionInput = new ActionInput();
        foreach ($requiredAttributes as $attributeId) {
            list($contextId, $attributeId) = ContextParser::determineContextAndAttributeId($attributeId);
            $attribute = $this->contextService->getAttribute($attributeId, $contextId);
            $actionInput->addAttribute($attribute);
        }
        return $actionInput;
    }
}
