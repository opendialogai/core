<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationStore;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\Core\Conversation\ConversationDataClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class ConversationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(ConversationEngineInterface::class, function () {
            $conversationEngine = new ConversationEngine();
            return $conversationEngine;
        });

        $this->app->singleton(ConversationDataClient::class, function () {
            return new ConversationDataClient(
                config('opendialog.core.DGRAPH_URL'),
                config('opendialog.core.DGRAPH_PORT'),
                config('opendialog.core.DGRAPH_API_KEY'),
            );
        });
    }
}
