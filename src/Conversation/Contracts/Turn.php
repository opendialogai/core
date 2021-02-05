<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

interface Turn extends ConversationObject
{
    public function getScene(): Scene;
}
