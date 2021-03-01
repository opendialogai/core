<?php

namespace OpenDialogAi\ConversationEngine\Facades\Selectors;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;

/**
 * @method static SceneCollection selectStartingScenes(ConversationCollection $conversations, bool $shallow = true)
 * @method static SceneCollection selectOpenScenes(ConversationCollection $conversations, bool $shallow = true)
 * @method static SceneCollection selectScenes(ConversationCollection $conversations, bool $shallow = true)
 * @throws EmptyCollectionException
 */
class SceneSelector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ConversationEngine\Selectors\SceneSelector::class;
    }
}
