<?php

namespace OpenDialogAi\InterpreterEngine;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use OpenDialogAi\InterpreterEngine\Service\InterpreterService;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\Exceptions\DefaultInterpreterNotDefined;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\QnA\QnAClient;

class InterpreterEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-interpreterengine-custom.php' => config_path('opendialog/interpreter_engine.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-interpreterengine.php', 'opendialog.interpreter_engine');

        $this->app->bind(LuisClient::class, function () {
            $config = config('opendialog.interpreter_engine.luis_config');
            return new LuisClient(new Client(), $config);
        });

        $this->app->bind(QnAClient::class, function () {
            $config = config('opendialog.interpreter_engine.qna_config');
            return new QnAClient(new Client(), $config);
        });

        $this->app->singleton(InterpreterServiceInterface::class, function () {
            $interpreterService = new InterpreterService();
            $interpreterService->registerAvailableInterpreters(config('opendialog.interpreter_engine.available_interpreters'));

            // Register custom interpreters if they are available
            if (is_array(config('opendialog.interpreter_engine.custom_interpreters'))) {
                $interpreterService->registerAvailableInterpreters(config('opendialog.interpreter_engine.custom_interpreters'));
            }

            // Check if there is a default interpreter that is amongst the registered ones and set it as such
            $defaultInterpreterName = config('opendialog.interpreter_engine.default_interpreter');
            if ($interpreterService->isInterpreterAvailable($defaultInterpreterName)) {
                $interpreterService->setDefaultInterpreter($defaultInterpreterName);
            } else {
                throw new DefaultInterpreterNotDefined('You must define a default interpreter for OpenDialog.');
            }

            // Check that there is a callback interpreter and setup the supported callbacks
            if ($interpreterService->isInterpreterAvailable(CallbackInterpreter::getName())) {
                /* @var CallbackInterpreter $interpreter */
                $interpreter = $interpreterService->getInterpreter(CallbackInterpreter::getName());
                $interpreter->setSupportedCallbacks(config('opendialog.interpreter_engine.supported_callbacks'));
            }
            return $interpreterService;
        });
    }
}
