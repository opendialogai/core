<?php

return [
    /**
     * A registration of known RASA entities mapped to known attribute type. If an entity is returned from RASA that is
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

    'default_interpreter' => 'interpreter.core.callbackInterpreter',

    /**
     * List of supported intents in the format 'callback_id' => 'intent_name'
     */
    'supported_callbacks' => [
        'WELCOME' => 'intent.core.welcome',
    ]
];
