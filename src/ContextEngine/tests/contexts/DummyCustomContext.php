<?php


namespace OpenDialogAi\ContextEngine\tests\contexts;

use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;
use OpenDialogAi\Core\Attribute\IntAttribute;

/**
 * Just used for tests
 */
class DummyCustomContext extends AbstractCustomContext
{
    public static $name = 'dummy_context';

    public function loadAttributes(): void
    {
        $this->addAttribute(new IntAttribute('1', 1));
        $this->addAttribute(new IntAttribute('2', 2));
        $this->addAttribute(new IntAttribute('3', 3));
    }
}
