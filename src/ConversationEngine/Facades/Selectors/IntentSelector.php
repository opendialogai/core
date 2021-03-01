<?php

namespace OpenDialogAi\ConversationEngine\Facades\Selectors;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * @method static IntentCollection selectRequestIntents(TurnCollection $turns, bool $shallow = true)
 * @method static IntentCollection selectResponseIntents(TurnCollection $turns, bool $shallow = true)
 * @method static IntentCollection selectRequestIntentsById(TurnCollection $turns, string $intentId, bool $shallow = true)
 * @method static IntentCollection selectResponseIntentsById(TurnCollection $turns, string $intentId, bool $shallow = true)
 * @throws EmptyCollectionException
 */
class IntentSelector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ConversationEngine\Selectors\IntentSelector::class;
    }
}
