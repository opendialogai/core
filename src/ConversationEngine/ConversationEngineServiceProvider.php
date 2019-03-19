<?php

namespace OpenDialogAi\ConversationEngine;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ConversationEngine\Service\ConversationEngineService;

class ConversationEngineServiceProvider extends ServiceProvider
{
    const CONVERSATION_ENGINE_SERVICE = 'conversation-engine-service';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-conversationengine.php' => base_path('config/opendialog-conversationengine.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-conversationengine.php', 'opendialog.conversation_engine');

        $this->app->bind(self::CONVERSATION_ENGINE_SERVICE, function () {
            return new ConversationEngineService();
        });
    }
}
