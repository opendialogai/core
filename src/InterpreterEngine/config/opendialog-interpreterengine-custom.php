<?php

return [
    /**
     * A registration of know LUIS entities mapped to known attribute type. If an entity is returned from LUIS that is
     * not an already registered attribute name and is not mapped here, a StringAttribute will be used
     *
     * Mapping is {luis_entity_type} => {OD_attribute_name}
     */
    'luis_entities' => [
//         'example_type' => 'first_name'
    ],

    /**
     * A registration of know RASA entities mapped to known attribute type. If an entity is returned from RASA that is
     * not an already registered attribute name and is not mapped here, a StringAttribute will be used
     *
     * Mapping is {rasa_entity_type} => {OD_attribute_name}
     */
    'rasa_entities' => [
//         'example_type' => 'first_name'
    ],

    /**
     * Custom interpreters registered in the format
     */
    'custom_interpreters' => [
//    \OpenDialogAi\InterpreterEngine\tests\Interpreters\DummyInterpreter::class
    ],

    /**
     * Cache time in seconds for each interpreter
     */
    'interpreter_cache_times' => [
//        'interpreter.core.luis' => 60,
    ],

    'default_interpreter' => 'interpreter.core.callbackInterpreter',

    /**
     * List of supported intents in the format 'callback_id' => 'intent_name'
     */
    'supported_callbacks' => [
//        'WELCOME' => 'intent.core.welcome',
    ]
];
