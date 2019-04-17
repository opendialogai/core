<?php

namespace OpenDialogAi\ConversationLog;

use Illuminate\Support\ServiceProvider;

class ConversationLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
    }
}
