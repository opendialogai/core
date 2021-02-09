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
        $this->put($object->getOdId(), $object);
    }

    public function getObject($odId)
    {
        $object = $this->get($odId);
        // We need this to cast object to appropriate class
        switch (true) {
            case ($object instanceof Scenario):
                return $object;
                break;
            case ($object instanceof Conversation):
                return $object;
                break;
            case ($object instanceof Scene):
                return $object;
                break;
            case ($object instanceof Turn):
                return $object;
                break;
            case ($object instanceof Intent):
                return $object;
                break;
            default:
                return null;
        }
    }
}
