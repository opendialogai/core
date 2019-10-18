<?php

namespace OpenDialogAi\Core\Conversation;


use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * A UserAttribute is an piece of data that can be stored against a ChatbotUser.
 */
class UserAttribute extends Node
{
    public function __construct($id, $type, $value)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::USER_ATTRIBUTE));
        $this->addAttribute(new StringAttribute(Model::USER_ATTRIBUTE_NAME, $id));
        $this->addAttribute(new StringAttribute(Model::USER_ATTRIBUTE_TYPE, $type));
        $this->addAttribute(new StringAttribute(Model::USER_ATTRIBUTE_VALUE, $value));
    }
}
