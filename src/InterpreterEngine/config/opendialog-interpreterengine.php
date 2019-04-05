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
     * A registration of know LUIS entities mapped to known attribute type. If an entity is returned from LUIS that is
     * not an already registered attribute name and is not mapped here, a StringAttribute will be used
     *
     * Mapping is {luis_entity_type} => {OD_attribute_name}
     */
    'luis_entities' => [
//         'example_type' => 'first_name'
    ],

    // Register the application interfaces
    'available_interpreters' => [
        OpenDialogAi\InterpreterEngine\Interpreters\LuisInterpreter::class
    ]
];
