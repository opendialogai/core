<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An action is something performed that may change Attributes.
 */
class Action extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::ACTION));
    }
}
