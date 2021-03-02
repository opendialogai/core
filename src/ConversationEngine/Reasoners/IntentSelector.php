<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Util\SelectorUtil;
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
     * @throws EmptyCollectionException
     */
    public static function selectRequestIntents(TurnCollection $turns, bool $shallow = true): IntentCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllRequestIntents($turns, $shallow);

        return self::interpretAndFilterIntents($intents);
    }

    /**
     * Retrieves all response intents within the given turns
     *
     * @param TurnCollection $turns
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectResponseIntents(TurnCollection $turns, bool $shallow = true): IntentCollection
    {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllResponseIntents($turns, $shallow);

        return self::interpretAndFilterIntents($intents);
    }

    /**
     * Retrieves all request intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectRequestIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllRequestIntentsById($turns, $intentId, $shallow);

        return self::interpretAndFilterIntents($intents);
    }

    /**
     * Retrieves all response intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectResponseIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllResponseIntentsById($turns, $intentId, $shallow);

        return self::interpretAndFilterIntents($intents);
    }

    /**
     * @param IntentCollection $intents
     * @return IntentCollection
     */
    private static function interpretAndFilterIntents(IntentCollection $intents): IntentCollection
    {
        $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
        $matchingIntents = IntentInterpreterFilter::filter($intents, $utterance);

        /** @var IntentCollection $intentsWithPassingConditions */
        $intentsWithPassingConditions = ConditionFilter::filterObjects($matchingIntents);

        return $intentsWithPassingConditions;
    }
}
