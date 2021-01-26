<?php

namespace OpenDialogAi\ContextEngine\Contexts\Intent;

use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;

class IntentContext extends AbstractContext
{
    public const INTENT_CONTEXT = '_intent';

    public function __construct()
    {
        parent::__construct(self::INTENT_CONTEXT);
    }

    public function refresh(): void
    {
        /** @var AttributeInterface $attribute */
        foreach ($this->getAttributes() as $attribute) {
            $this->removeAttribute($attribute->getId());
        }
    }
}
