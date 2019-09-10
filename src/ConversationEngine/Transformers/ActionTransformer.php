<?php

namespace OpenDialogAi\ConversationEngine\Transformers;

use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Model;

/**
 * Generates an Action Node from a DGraph response
 */
class ActionTransformer
{
    public static function toAction($data): Action
    {
        $action = new Action($data[Model::ID]);
        return $action;
    }
}
