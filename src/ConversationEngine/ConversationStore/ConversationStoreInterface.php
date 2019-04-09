<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;

interface ConversationStoreInterface
{
    public function getAllOpeningIntents(): Map;
}
