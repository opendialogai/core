<?php

namespace OpenDialogAi\ContextEngine\Contexts\Intent;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;

class IntentContext extends AbstractContext
{
    public const INTENT_CONTEXT = '_intent';
    protected static ?string $componentId = self::INTENT_CONTEXT;

    public function __construct()
    {
        parent::__construct();
    }

    public function refresh(): void
    {
        /** @var Attribute $attribute */
        foreach ($this->getAttributes() as $attribute) {
            $this->removeAttribute($attribute->getId());
        }
    }
}
