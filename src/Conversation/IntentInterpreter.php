<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * An IntentInterpreter interprets intents.
 */
class IntentInterpreter extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::INTENT_INTERPRETER));
    }
}
