<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\Node\Node;

class VirtualIntent extends Node
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->setGraphType(DGraphClient::VIRTUAL_INTENT);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::VIRTUAL_INTENT));
    }
}
