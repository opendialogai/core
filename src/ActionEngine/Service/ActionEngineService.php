<?php

namespace OpenDialogAi\ActionEngine\Service;

use ActionEngine\Exceptions\ActionNameNotSetException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\Core\Attribute\AttributeInterface;

class ActionEngineService implements ActionEngineServiceInterface
{
    /** @var AttributeResolverService */
    private $attributeResolver;

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

    public function setAttributeResolver(AttributeResolverService $attributeResolver)
    {
        $this->attributeResolver = $attributeResolver;
    }

    public function getAvailableActions()
    {
        return $this->availableActions;
    }

    public function performAction(string $actionName)
    {
    }

    public function resolveAttributes(ActionInterface $action)
    {
        /** @var AttributeInterface $attribute */
        foreach ($action->requiresAttributes() as $attribute) {
            $value = $this->attributeResolver->getAttributeFor($attribute->getId());
            $action->setAttributeValue($attribute, $value);
        }
    }
}
