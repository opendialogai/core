<?php

namespace OpenDialogAi\ContextEngine\Contracts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * A scoped context accepts parameters that it will use to scope data access based on its implementation
 * of data access (such as the use of custom data clients to access persistent storage).
 */
interface ScopedContext extends Context
{
    /**
     * A scope is a series of parameters such as
     *   [
     *      'user_id' => '1234'
     *      'user_group' => 'logged_in'
     *    ]
     * The semantics of parameters are up to the contexts to interpret and use.
     *
     * @param array $parameters
     */
    public function setScope(array $parameters): void;

    /**
     * Returns the parameters that were set.
     *
     * @return array
     */
    public function getScope(): array;
}
