<?php

namespace OpenDialogAi\Core\Conversation\Contracts;

/**
 * An conversation object collection holds a set of conversation objects that can be set or retrieved
 */
interface ObjectCollection
{
    /**
     * Adds an attribute to the map of attributes
     *
     * @param ConversationObject $conversationObject
     */
    public function addObject(ConversationObject $conversationObject);

    /**
     * Tries to get a Conversation Object if it exists
     *
     * @param $objectId
     * @return ConversationObject
     */
    public function getObject(string $objectId): ConversationObject;

    /**
     * Checks whether the ConversationObject with given id exists
     *
     * @param $objectId string
     * @return bool
     */
    public function hasObject($objectId): bool;
}
