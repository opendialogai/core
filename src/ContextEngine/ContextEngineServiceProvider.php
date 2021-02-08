<?php

namespace OpenDialogAi\ContextEngine;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserDataClient;
use OpenDialogAi\ContextEngine\ContextService\CoreContextService;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\ContextEngine\Contexts\Intent\IntentContext;
use OpenDialogAi\ContextEngine\Contexts\MessageHistory\MessageHistoryContext;

class ContextEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-contextengine-custom.php' => config_path('opendialog/context_engine.php'),
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-contextengine.php', 'opendialog.context_engine');

        $this->app->singleton(ContextService::class, function () {
            $contextService = new CoreContextService();

            $contextService->createContext(CoreContextService::SESSION_CONTEXT);
            $contextService->createContext(CoreContextService::CONVERSATION_CONTEXT);
            $contextService->addContext(new UserContext($this->app->make(UserDataClient::class)));
            $contextService->addContext(new IntentContext());
            $contextService->addContext(new MessageHistoryContext());

            if (is_array(config('opendialog.context_engine.custom_contexts'))) {
                $contextService->loadCustomContexts(config('opendialog.context_engine.custom_contexts'));
            }

            return $contextService;
        });

        $this->app->singleton(UserDataClient::class, function() {
            return new UserDataClient(

            );
        });

//        $this->app->singleton(UserService::class, function () {
//            return new UserService(
//                $this->app->make(DGraphClient::class),
//                $this->app->make(ConversationStoreInterface::class)
//            );
//        });
    }
}
