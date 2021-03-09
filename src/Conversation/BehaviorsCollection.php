<?php

namespace OpenDialogAi\Core\Conversation;

use Illuminate\Support\Collection;

/**
 * An condition collection holds a set of condition objects that can be set or retrieved
 */
class BehaviorsCollection extends Collection
{
    public function hasBehavior(string $behaviorId): bool
    {
        return $this->filter(function (Behavior $behavior) use ($behaviorId) {
            return $behavior->getBehavior() == $behaviorId;
        })->isNotEmpty();
    }
}
