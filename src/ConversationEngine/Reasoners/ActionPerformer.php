<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

class ActionPerformer
{
    /**
     * Performs all actions associated with the given intents
     *
     * @param IntentCollection $intents
     */
    public static function performActionsForIntents(IntentCollection $intents): void
    {
        foreach ($intents as $intent) {
            self::performActionsForIntent($intent);
        }
    }
    /**
     * Performs all actions associated with the given intent
     *
     * @param Intent $intent
     */
    public static function performActionsForIntent(Intent $intent): void
    {
        self::performActions($intent->getActions());
    }

    /**
     * Performs all actions in the given collection
     *
     * @param ActionsCollection|Action[] $actions
     */
    public static function performActions(ActionsCollection $actions): void
    {
        foreach ($actions as $action) {
            self::performAction($action);
        }
    }

    /**
     * Performs the given action
     *
     * @param Action $action
     */
    public static function performAction(Action $action): void
    {
        $inputAttributes = $action->getInputAttributes();

        $actionResult = resolve(ActionEngineInterface::class)->performAction(
            $action->getOdId(),
            $inputAttributes
        );

        // Store desired result attributes in desired contents (use session otherwise)
        $resultAttributes = $actionResult->getResultAttributes();
        $contextAttributeMap = self::createContextMap($inputAttributes, $resultAttributes);

        self::saveAttributesToContexts($contextAttributeMap, $resultAttributes);

        // Loop thru dirtied contexts and persist
        self::persistUpdatedContexts($contextAttributeMap);
    }

    /**
     * @param Collection $parsedInputAttributes
     * @param BasicAttributeBag $resultAttributes
     * @return Collection
     */
    private static function createContextMap(Collection $parsedInputAttributes, BasicAttributeBag $resultAttributes): Collection
    {
        return collect($resultAttributes->getAttributes()->toArray())
            ->mapWithKeys(function (Attribute $attribute) use ($parsedInputAttributes) {
                $context = $parsedInputAttributes->has($attribute->getId())
                    ? $parsedInputAttributes->get($attribute->getId())
                    : SessionContext::getComponentId();

                return [ $attribute->getId() => $context ];
            });
    }

    /**
     * @param Collection $contextAttributeMap
     * @param BasicAttributeBag $resultAttributes
     */
    private static function saveAttributesToContexts(Collection $contextAttributeMap, BasicAttributeBag $resultAttributes): void
    {
        foreach ($contextAttributeMap as $attributeId => $contextId) {
            $attribute = $resultAttributes->getAttribute($attributeId);

            try {
                $context = ContextService::getContext($contextId);
            } catch (ContextDoesNotExistException $e) {
                $context = ContextService::getSessionContext();
                Log::debug(sprintf(
                    "'%s' saved to session context as provided content '%s' is not registered.",
                    $attributeId,
                    $contextId
                ));
            }

            $context->addAttribute($attribute);
        }
    }

    /**
     * @param Collection $contextAttributeMap
     */
    private static function persistUpdatedContexts(Collection $contextAttributeMap): void
    {
        $updatedContextIds = $contextAttributeMap->values()->unique();
        foreach ($updatedContextIds as $updatedContextId) {
            $updatedContext = ContextService::getContext($updatedContextId);
            $persistenceSuccessful = $updatedContext->persist();

            if (!$persistenceSuccessful) {
                Log::warning(sprintf(
                    "Attempted to persist context '%s' but was unsuccessful.", $updatedContextId->getId()
                ));
            }
        }
    }
}
