<?php

return [

    // Config for the LUIS service
    'luis_config' => [
        'app_url'          => env('LUIS_APP_URL'),
        'app_id'           => env('LUIS_APP_ID'),
        'subscription_key' => env('LUIS_SUBSCRIPTION_KEY'),
        'staging'          => env('LUIS_STAGING', 'false'),
        'timezone_offset'  => env('LUIS_TIMEZONE_OFFSET', 0),
        'verbose'          => env('LUIS_VERBOSE', 'true'),
        'spellcheck'       => env('LUIS_SPELLCHECK', 'true')
    ],

    /*
     * A registration of know LUIS entities mapped to attribute type. If an entity is returned from LUIS that is not
     * defined here, the default type of StringAttribute will be used
     */
    'luis_entities' => [
//         'example_type' => \OpenDialogAi\Core\Attribute\StringAttribute::class
    ],

    // Register the application interfaces
    'available_interpreters' => [
         \InterpreterEngine\Interpreters\LuisInterpreter::class
    ]
];
