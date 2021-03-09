<?php


namespace OpenDialogAi\ConversationEngine\Selectors;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\ConversationEngine\Reasoners\IntentInterpreterFilter;
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
     * @param bool $isIncoming
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectRequestIntents(
        TurnCollection $turns,
        bool $isIncoming = true,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllRequestIntents($turns, $shallow);

        return self::interpretAndFilterIntents($intents, $isIncoming);
    }

    /**
     * Retrieves all response intents within the given turns
     *
     * @param TurnCollection $turns
     * @param bool $isIncoming
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectResponseIntents(
        TurnCollection $turns,
        bool $isIncoming = true,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllResponseIntents($turns, $shallow);

        return self::interpretAndFilterIntents($intents, $isIncoming);
    }

    /**
     * Retrieves all request intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $isIncoming
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectRequestIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $isIncoming = true,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllRequestIntentsById($turns, $intentId, $shallow);

        return self::interpretAndFilterIntents($intents, $isIncoming);
    }

    /**
     * Retrieves all response intents that match the given intent ID, within the given turns
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $isIncoming
     * @param bool $shallow
     * @return IntentCollection
     * @throws EmptyCollectionException
     */
    public static function selectResponseIntentsById(
        TurnCollection $turns,
        string $intentId,
        bool $isIncoming = true,
        bool $shallow = true
    ): IntentCollection {
        SelectorUtil::throwIfConversationObjectCollectionIsEmpty($turns);

        $intents = ConversationDataClient::getAllResponseIntentsById($turns, $intentId, $shallow);

        return self::interpretAndFilterIntents($intents, $isIncoming);
    }

    /**
     * @param IntentCollection $intents
     * @param bool $shouldInterpret
     * @return IntentCollection
     */
    private static function interpretAndFilterIntents(IntentCollection $intents, bool $shouldInterpret): IntentCollection
    {
        if ($shouldInterpret) {
            $utterance = ContextService::getAttribute(UtteranceAttribute::UTTERANCE, UserContext::USER_CONTEXT);
            $intents = IntentInterpreterFilter::filter($intents, $utterance);
        }

        /** @var IntentCollection $intentsWithPassingConditions */
        $intentsWithPassingConditions = ConditionFilter::filterObjects($intents, $shouldInterpret);

        return $intentsWithPassingConditions;
    }
}
