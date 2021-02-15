<?php

namespace OpenDialogAi\ContextEngine\Contracts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * A persistent context stores some attributes in persistent storage. Here we overise the get
 * functions of context to introduce an boolean that will indicate that even if we do have the
 * attribute in memory we should "refresh" it, i.e. go back to persistent storage to retrieve it.
 */
interface PersistentContext extends Context
{

    /**
     * Returns all the attributes currently associated with this context.
     *
     * @return Map
     */
    public function getAttributes(bool $refresh = false): Map;

    /**
     * Retrieves an attribute, if present, from the context. It is always up to the calling service to let us know
     * what context we should use.
     *
     * @param string $attributeName
     * @throws \OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException
     */
    public function getAttribute(string $attributeName, bool $refresh = false): Attribute;

    /**
     * Persist context value.
     * In the case where a context does not need to be persisted, it can do nothing, or just create a log message.
     *
     * @return bool true if successful, false if not
     */
    public function persist(): bool;
}
