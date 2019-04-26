<?php

namespace OpenDialogAi\Util;

use Illuminate\Support\ServiceProvider;

class UtilServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    public function register()
    {
    }
}
