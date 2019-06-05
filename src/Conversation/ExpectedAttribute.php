<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An expected attribute that should be extracted from a specific intent
 */
class ExpectedAttribute extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::EXPECTED_ATTRIBUTE));
    }
}
