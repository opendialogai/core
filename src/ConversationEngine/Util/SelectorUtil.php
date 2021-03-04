<?php


namespace OpenDialogAi\ConversationEngine\Util;


use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\ODObjectCollection;

class SelectorUtil
{
    /**
     * @param ODObjectCollection $collection
     * @throws EmptyCollectionException
     */
    public static function throwIfConversationObjectCollectionIsEmpty(ODObjectCollection $collection): void
    {
        if ($collection->isEmpty()) {
            throw new EmptyCollectionException();
        }
    }
}
