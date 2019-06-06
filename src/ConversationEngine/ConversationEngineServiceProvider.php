<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationStore;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class ConversationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(ConversationStoreInterface::class, function () {
            return new DGraphConversationStore($this->app->make(DGraphClient::class));
        });

        $this->app->singleton(ConversationEngineInterface::class, function () {
            $conversationEngine = new ConversationEngine();
            $conversationEngine->setConversationStore($this->app->make(DGraphConversationStore::class));

            $interpreterService = $this->app->make(InterpreterServiceInterface::class);
            $conversationEngine->setInterpreterService($interpreterService);

            $actionEngine = $this->app->make(ActionEngineInterface::class);
            $conversationEngine->setActionEngine($actionEngine);

            $attributeResolver = $this->app->make(AttributeResolver::class);
            $conversationEngine->setAttributeResolver($attributeResolver);

            $contextService = $this->app->make(ContextService::class);
            $conversationEngine->setContextService($contextService);

            return $conversationEngine;
        });
    }
}
