<?php

namespace OpenDialogAi\ConversationEngine\Facades\Selectors;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * @method static ConversationCollection selectStartingConversations(ScenarioCollection $scenarios, bool $shallow = true)
 * @method static ConversationCollection selectOpenConversations(ScenarioCollection $scenarios, bool $shallow = true)
 * @method static ConversationCollection selectConversations(ScenarioCollection $scenarios, bool $shallow = true)
 * @throws EmptyCollectionException
 */
class ConversationSelector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ConversationEngine\Selectors\ConversationSelector::class;
    }
}
