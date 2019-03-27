<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An action is something performed that may change Attributes.
 */
class Action extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::ACTION));
    }
}
