<?php

namespace OpenDialogAi\ContextEngine\Contexts\Intent;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;

class IntentContext extends BaseContext
{
    public const INTENT_CONTEXT = '_intent';

    public function __construct()
    {
        parent::__construct(self::INTENT_CONTEXT);
    }

    public function refresh(): void
    {
        /** @var Attribute $attribute */
        foreach ($this->getAttributes() as $attribute) {
            $this->removeAttribute($attribute->getId());
        }
    }
}
