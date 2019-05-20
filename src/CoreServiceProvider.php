<?php

namespace OpenDialogAi\Core;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
use OpenDialogAi\Core\Console\Commands\ExportConversation;
use OpenDialogAi\Core\Console\Commands\ImportConversation;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class CoreServiceProvider extends ServiceProvider
{
    /** @var requestId */
    private $requestId;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/opendialog.php' => base_path('config/opendialog/core.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../dgraph' => base_path('dgraph')
        ], 'dgraph');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportConversation::class,
                ImportConversation::class,
            ]);
        }

        $this->requestId = uniqid();
        $this->app->when('OpenDialogAi\Core\Http\Middleware\RequestLoggerMiddleware')
            ->needs('$requestId')
            ->give($this->requestId);

        Log::pushProcessor(LoggingHelper::getLogUserIdProcessor($this->requestId));
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/opendialog.php', 'opendialog.core');

        $this->app->singleton(OpenDialogController::class, function () {
            return new OpenDialogController();
        });

        $this->app->singleton(DGraphClient::class, function () {
            return new DGraphClient(
                config('opendialog.core.DGRAPH_URL'),
                config('opendialog.core.DGRAPH_PORT')
            );
        });

        $this->app->singleton(OpenDialogController::class, function () {
            $odController = new OpenDialogController();

            $odController->setContextService($this->app->make(ContextService::class));
            $odController->setConversationLogService($this->app->make(ConversationLogService::class));
            $odController->setConversationEngine($this->app->make(ConversationEngineInterface::class));
            $odController->setResponseEngine($this->app->make(ResponseEngineServiceInterface::class));

            return $odController;
        });
    }
}
