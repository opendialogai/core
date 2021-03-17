<?php


namespace OpenDialogAi\ContextEngine\Tests\Contexts;


use OpenDialogAi\ContextEngine\Contexts\PersistentContext;
use OpenDialogAi\ContextEngine\DataClients\GraphAttributeDataClient;

class ExamplePersistentContext extends PersistentContext
{
    protected static string $componentId = 'example_persistent_context';

    public function __construct(GraphAttributeDataClient $dataClient)
    {
        parent::__construct($dataClient);
    }
}
