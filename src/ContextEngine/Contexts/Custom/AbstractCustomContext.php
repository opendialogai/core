<?php

namespace OpenDialogAi\ContextEngine\Contexts\Custom;

use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;

/**
 * An abstract implementation of a custom context.
 *
 * Custom contexts must manage their external dependencies themselves. They will not be loaded into the service layer
 * or have any dependencies injected.
 *
 * They should be registered in the 'custom_contexts' section of the opendialog-contextengine config file
 *
 * The Context Manager will loop though all registered custom contexts, instantiate them and call the
 * @see AbstractCustomContext::loadAttributes() method to make the custom context attributes available to the application
 */
abstract class AbstractCustomContext extends AbstractContext
{
    /**
     * The name to give the custom context.
     *
     * @var string
     */
    public static $name = '';

    /**
     * AbstractCustomContext constructor.
     */
    public function __construct()
    {
        parent::__construct(static::$name);
    }

    /**
     * A function to load all custom attributes from any external sources into this custom context.
     *
     * All attributes should be added using @see AbstractContext::addAttribute()
     */
    abstract public function loadAttributes(): void;
}
