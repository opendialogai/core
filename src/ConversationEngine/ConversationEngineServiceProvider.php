<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationStore;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class ConversationEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-conversationengine.php' => base_path('config/opendialog-conversationengine.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-conversationengine.php', 'opendialog.conversation_engine');

        $this->app->singleton(ConversationEngineInterface::class, function () {
           return new DGraphConversationStore($this->app->make(DGraphClient::class));
        });

        $this->app->singleton(ConversationEngineInterface::class, function () {
            $conversationEngine = new ConversationEngine();
            $conversationEngine->setConversationStore($this->app->make(DGraphConversationStore::class));
            return $conversationEngine;
        });
    }
}
