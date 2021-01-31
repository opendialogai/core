<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An expected attribute that should be extracted from a specific intent
 */
class ExpectedAttribute extends Node
{
    protected static $idIsUnique = false;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::EXPECTED_ATTRIBUTE));
    }
}
