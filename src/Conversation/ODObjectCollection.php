<?php

namespace OpenDialogAi\Core\Conversation;


use Illuminate\Support\Collection;
use OpenDialogAi\Core\Conversation\Exceptions\CannotAddObjectWithoutODidException;

/**
 * An standard OD object collection holds OpenDialog conversation objects
 */
class ODObjectCollection extends Collection
{
    public function addObject(ConversationObject $object)
    {
        $odId = $object->getODId();
        if (!isset($odId) || $odId == ConversationObject::UNDEFINED) {
            throw new CannotAddObjectWithoutODidException();
        }
        $this->push($object);
    }

    /**
     * @param $odid
     */
    public function getObjectsWithId(string $odId): ODObjectCollection
    {
        $filtered = $this->filter(function (ConversationObject $object) use ($odId) {
            if ($object->getODId() == $odId) {
                return true;
            }
            return false;
        });

        return $filtered;
    }
}
