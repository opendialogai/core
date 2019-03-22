<?php

namespace OpenDialogAi\ActionEngine\Service;

use ActionEngine\Exceptions\ActionNameNotSetException;
use ActionEngine\Exceptions\ActionNotAvailableException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Results\ActionResult;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;

class ActionEngineService implements ActionEngineServiceInterface
{
    /** @var AttributeResolverService */
    private $attributeResolver;

    /** @var ActionInterface[] */
    private $availableActions = [];

    /**
     * @inheritdoc
     */
    public function setAvailableActions($supportedActions): void
    {
        foreach ($supportedActions as $supportedAction) {
            try {
                if (!class_exists($supportedAction)) {
                    Log::warning(
                        sprintf(
                            "Skipping adding action %s to list of supported actions as it does not exist",
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
     * @param AttributeResolverService $attributeResolver
     */
    public function setAttributeResolver(AttributeResolverService $attributeResolver)
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableActions(): array
    {
        return $this->availableActions;
    }

    /**
     * @inheritdoc
     */
    public function performAction(string $actionName): ActionResult
    {
        if ($this->actionIsAvailable($actionName)) {
            return $this->availableActions[$actionName]->perform();
        }

        throw new ActionNotAvailableException(
            sprintf(
                "Action %s is not available and cannot be performed",
                $actionName
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function resolveAttributes(ActionInterface $action)
    {
        foreach ($action->requiresAttributes() as $attribute) {
            $value = $this->attributeResolver->getAttributeFor($attribute);
            $action->setAttributeValue($attribute, $value);
        }
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
}
