<?php

namespace OpenDialogAi\ContextEngine\Tests\Contexts;

use OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext;

class BadlyNamedCustomContext extends AbstractCustomContext
{
    public function loadAttributes(): void
    {
        // empty
    }
}
