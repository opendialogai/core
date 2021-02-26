<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * This selector provides methods that select various types of intents and filters them by evaluating their conditions
 */
class IntentSelector
{
    /**
     * Retrieves all request intents within the given turns
     *
     * @param TurnCollection $turns
     * @param bool $shallow
     * @return IntentCollection
     */
    public static function selectRequestIntents(TurnCollection $turns, bool $shallow = true): IntentCollection
    {
        // These are all the possible intents that could start a conversation
        /** @var IntentCollection $intents */
        $intents = ConversationDataClient::getAllRequestIntents($turns);

        // Now we can pass each intent through interpreters and interpret given the utterance
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        $matchingIntents = IntentInterpreterFilter::filter($intents, $utterance);

        // We first reduce the set to just those that have passing conditions
        /** @var IntentCollection $intentsWithPassingConditions */
        $intentsWithPassingConditions = ConditionFilter::filterObjects($matchingIntents);

        return $intentsWithPassingConditions;
    }

    /**
     * Retrieves all response intents within the given turns
     *
     * @param TurnCollection $turns
     * @param bool $shallow
     * @return IntentCollection
     */
    public static function selectResponseIntents(TurnCollection $turns, bool $shallow = true): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieves all request intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     */
    public static function selectRequestIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $shallow = true
    ): IntentCollection {
        return new IntentCollection();
    }

    /**
     * Retrieves all response intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     */
    public static function selectResponseIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $shallow = true
    ): IntentCollection {
        return new IntentCollection();
    }
}
