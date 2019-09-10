<?php


namespace OpenDialogAi\ContextEngine\tests\contexts;

use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;

class BadlyNamedCustomContext extends AbstractCustomContext
{
    public function loadAttributes(): void
    {
        // empty
    }
}
