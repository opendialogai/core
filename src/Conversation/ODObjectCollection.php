<?php

namespace OpenDialogAi\Core\Conversation;


use Illuminate\Support\Collection;
use OpenDialogAi\Core\Conversation\Exceptions\CannotAddObjectWithoutODidException;

/**
 * An standard OD object collection holds OpenDialog conversation objects
 */
class ODObjectCollection extends Collection
{
    /**
     * @param ConversationObject $object
     * @throws CannotAddObjectWithoutODidException
     */
    public function addObject(ConversationObject $object): void
    {
        $odId = $object->getODId();

        if (!isset($odId) || $odId == ConversationObject::UNDEFINED) {
            throw new CannotAddObjectWithoutODidException();
        }

        $this->push($object);
    }

    /**
     * @param string $odId
     * @return ODObjectCollection
     */
    public function getObjectsWithId(string $odId): ODObjectCollection
    {
        return $this->filter(function (ConversationObject $object) use ($odId) {
            return $object->getODId() == $odId;
        });
    }
}
