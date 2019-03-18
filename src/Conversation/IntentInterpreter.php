<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Graph\Node\Node;

class IntentInterpreter extends Node
{
    public function __construct($id)
    {
        parent::__construct();
        $this->setId($id);
    }
}
