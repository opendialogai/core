<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An interpreter to be used to interpret a specific intent
 */
class Interpreter extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::INTENT_INTERPRETER));
    }
}
