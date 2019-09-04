<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationStore;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class ConversationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(EIModelCreator::class);

        $this->app->singleton(ConversationQueryFactoryInterface::class, function () {
            return new DGraphConversationQueryFactory();
        });

        $this->app->singleton(ConversationStoreInterface::class, function () {
            return new DGraphConversationStore(
                $this->app->make(DGraphClient::class),
                $this->app->make(EIModelCreator::class),
                $this->app->make(ConversationQueryFactoryInterface::class)
            );
        });

        $this->app->singleton(ConversationEngineInterface::class, function () {
            $conversationEngine = new ConversationEngine();
            $conversationEngine->setConversationStore($this->app->make(ConversationStoreInterface::class));

            $interpreterService = $this->app->make(InterpreterServiceInterface::class);
            $conversationEngine->setInterpreterService($interpreterService);

            $actionEngine = $this->app->make(ActionEngineInterface::class);
            $conversationEngine->setActionEngine($actionEngine);

            return $conversationEngine;
        });
    }
}
