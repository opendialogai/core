<?php

return [

    'luis_config' => [
        'app_url'          => env('LUIS_APP_URL'),
        'app_id'           => env('LUIS_APP_ID'),
        'subscription_key' => env('LUIS_SUBSCRIPTION_KEY'),
        'staging'          => env('LUIS_STAGING', 'false'),
        'timezone_offset'  => env('LUIS_TIMEZONE_OFFSET', 0),
        'verbose'          => env('LUIS_VERBOSE', 'true'),
        'spell_check'      => env('LUIS_SPELLCHECK', 'true')
    ],

    // Register the application interfaces
    'available_interpreters' => [
        // \OpenDialogAi\InterpreterEngine\tests\Interpreters\DummyInterpreter::class,
    ]
];
