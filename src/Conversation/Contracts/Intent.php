<?php
namespace OpenDialogAi\Core\Conversation\Contracts;

use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;

interface Intent
{
    public function getScene(): Scene;
}
