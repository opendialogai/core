<?php

namespace OpenDialogAi\ConversationEngine\Facades\Selectors;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * @method static TurnCollection selectStartingTurns(SceneCollection $scenes, bool $shallow = true)
 * @method static TurnCollection selectOpenTurns(SceneCollection $scenes, bool $shallow = true)
 * @method static TurnCollection selectTurns(SceneCollection $scenes, bool $shallow = true)
 * @method static Turn selectTurnById(SceneCollection $scenes, string $turnId, bool $shallow = true)
 * @throws EmptyCollectionException
 */
class TurnSelector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ConversationEngine\Selectors\TurnSelector::class;
    }
}
