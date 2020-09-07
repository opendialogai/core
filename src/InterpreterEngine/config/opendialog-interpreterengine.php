<?php

return [

    'rasa_config' => [
        'app_url'      => env('RASA_APP_URL'),
    ],

    // Register the application interfaces
    'available_interpreters' => [
        OpenDialogAi\InterpreterEngine\Interpreters\RasaInterpreter::class,
        OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter::class
    ],

    'default_interpreter' => 'interpreter.core.callbackInterpreter',

    'default_cache_time' => 60,

    'supported_callbacks' => [
        'callback_example' => 'intent.core.exampleIntent',
        'chat_open' => 'intent.core.chatOpen'
    ]
];
