<?php


namespace OpenDialogAi\AttributeEngine\ContextManager;


use OpenDialogAi\AttributeEngine\Contexts\CurrentUserContext;

class ContextService
{
    const CONTEXT_SERVICE = 'context_service';

    public function __construct()
    {
        //@todo
    }

    public function getAvailableContexts()
    {
        return config('opendialog.attribute_engine.available_contexts');
    }

    public function isContextSupported(string $contextId)
    {
        //@todo
    }

    /**
     * @param string $contextId
     * @return ContextInterface
     */
    public function getContextFor(string $contextId)
    {
        /* @var ContextInterface $context */
        return new CurrentUserContext();
    }
}
