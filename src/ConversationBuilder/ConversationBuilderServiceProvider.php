<?php

namespace OpenDialogAi\ConversationBuilder;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ConversationBuilder\Service\ConversationBuilder;

class ConversationBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-conversationbuilder.php'
                => base_path('config/opendialog-conversationbuilder.php'),
            __DIR__ . '/config/activitylog.php'
                => base_path('config/activitylog.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/activitylog.php', 'activitylog');
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-conversationbuilder.php', 'opendialog.conversation_builder');

        $this->app->bind(ConversationBuilder::class, function () {
            return new ConversationBuilder();
        });
    }
}
