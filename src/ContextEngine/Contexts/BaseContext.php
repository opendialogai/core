<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;

class BaseContext extends AbstractContext
{
    use HasAttributesTrait;

    public function __construct($id)
    {
        parent::__construct($id);
    }
}

