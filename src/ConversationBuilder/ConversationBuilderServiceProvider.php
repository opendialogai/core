<?php

namespace OpenDialogAi\ConversationBuilder;

use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ConversationBuilder\Observers\ConversationObserver;
use OpenDialogAi\ConversationBuilder\Service\ConversationBuilder;

class ConversationBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/activitylog.php'
            => base_path('config/activitylog.php'),
        ], 'od-config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // Handle conversation life-cycle events.
        Conversation::observe(ConversationObserver::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/activitylog.php', 'activitylog');

        $this->app->bind(ConversationBuilder::class, function () {
            return new ConversationBuilder();
        });
    }
}
