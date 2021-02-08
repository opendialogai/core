<?php

namespace OpenDialogAi\ContextEngine\Tests\Contexts;

use OpenDialogAi\ContextEngine\Contexts\Custom\BaseCustomContext;

class BadlyNamedCustomContext extends BaseCustomContext
{
    public function loadAttributes(): void
    {
        // empty
    }
}
