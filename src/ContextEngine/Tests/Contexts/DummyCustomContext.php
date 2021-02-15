<?php

namespace OpenDialogAi\ContextEngine\Tests\Contexts;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;

/**
 * Just used for tests
 */
class DummyCustomContext extends AbstractCustomContext
{
    public static ?string $componentId = 'dummy_context';

    public function loadAttributes(): void
    {
        $this->addAttribute(new IntAttribute('1', 1));
        $this->addAttribute(new IntAttribute('2', 2));
        $this->addAttribute(new IntAttribute('3', 3));
    }
}
