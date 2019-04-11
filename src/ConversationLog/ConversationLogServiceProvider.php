<?php

namespace OpenDialogAi\ConversationLog;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ConversationLog\Service\ConversationLog;

class ConversationLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-conversationlog.php'
                => base_path('config/opendialog-conversationlog.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/opendialog-conversationlog.php',
            'opendialog.conversation_log'
        );

        // $this->app->bind(ChatbotUser::class, function () {
        //     return new ChatbotUser();
        // });
    }
}
