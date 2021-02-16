<?php

namespace OpenDialogAi\ContextEngine\Contexts\Intent;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;

class IntentContext extends AbstractContext
{
    public const INTENT_CONTEXT = '_intent';
    protected static string $componentId = self::INTENT_CONTEXT;

    protected static ?string $componentName = 'Intent';
    protected static ?string $componentDescription
        = 'A context managed by OpenDialog for storing data about each interpreted intent.';

    protected static bool $attributesAreReadOnly = true;

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
