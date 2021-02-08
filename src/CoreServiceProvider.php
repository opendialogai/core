<?php

namespace OpenDialogAi\Core;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use OpenDialogAi\ConversationLog\Service\ConversationLogService;
//use OpenDialogAi\Core\Console\Commands\ExportConversation;
//use OpenDialogAi\Core\Console\Commands\ImportConversation;
//use OpenDialogAi\Core\Console\Commands\ReadStatuses;
//use OpenDialogAi\Core\Console\Commands\StoreStatuses;
//use OpenDialogAi\Core\Console\Commands\UserAttributesCache;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Http\Middleware\RequestLoggerMiddleware;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * @var string $requestId
     */
    private $requestId;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/opendialog.php' => base_path('config/opendialog/core.php')
        ], 'opendialog-config');

        $this->publishes([
            __DIR__ . '/../../dgraph-docker' => base_path('dgraph')
        ], 'dgraph');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                //ExportConversation::class,
                //ImportConversation::class,
                //UserAttributesCache::class,
                //ReadStatuses::class,
                //StoreStatuses::class
            ]);
        }

        $this->requestId = uniqid('od-', true);
        $this->app->when(RequestLoggerMiddleware::class)
            ->needs('$requestId')
            ->give($this->requestId);

        if (env('INTROSPECTION_PROCESSOR_ENABLED', false)) {
            Log::pushProcessor(new IntrospectionProcessor(Logger::DEBUG, ['Illuminate\\']));
        }

        Log::pushProcessor(LoggingHelper::getLogUserIdProcessor($this->requestId));
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/opendialog.php', 'opendialog.core');

        $this->app->singleton(OpenDialogController::class, function () {
            $odController = new OpenDialogController();

            $odController->setConversationLogService($this->app->make(ConversationLogService::class));
//            $odController->setConversationEngine($this->app->make(ConversationEngineInterface::class));
            $odController->setResponseEngine($this->app->make(ResponseEngineServiceInterface::class));

            return $odController;
        });
    }
}
