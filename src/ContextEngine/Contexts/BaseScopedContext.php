<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use OpenDialogAi\ContextEngine\Contracts\ScopedContext;

class BaseScopedContext extends BaseContext implements ScopedContext
{
    protected array $parameters;

    public function __construct($id)
    {
        parent::__construct($id);
    }

    public function setScope(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getScope(): array
    {
        return $this->parameters;
    }
}
