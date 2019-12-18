<?php

use OpenDialogAi\Core\Conversation\Model;

return [
    'supported_attributes' => array(

        'attribute_name'  => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'attribute_value' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'callback_value'  => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        Model::CONTEXT    => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        Model::EI_TYPE    => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'operation'       => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'timestamp'       => \OpenDialogAi\Core\Attribute\IntAttribute::class,

        // QnA Interpreter
        'qna_answer' => \OpenDialogAi\Core\Attribute\StringAttribute::class,

        // Conversation Context
        'current_conversation' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'current_scene'        => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'current_intent'       => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'interpreted_intent'   => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'next_intents'         => \OpenDialogAi\Core\Attribute\ArrayAttribute::class,

        // Chatbot User
        'email'       => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'external_id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'first_name'  => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'full_name'   => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'id'          => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'last_name'   => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'age'         => \OpenDialogAi\Core\Attribute\IntAttribute::class,
        'name'        => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'last_seen'   => OpenDialogAi\Core\Attribute\TimestampAttribute::class,
        'first_seen'  => OpenDialogAi\Core\Attribute\TimestampAttribute::class,

        // Intents
        Model::CONFIDENCE => \OpenDialogAi\Core\Attribute\FloatAttribute::class,
        Model::COMPLETES  => \OpenDialogAi\Core\Attribute\BooleanAttribute::class,
        Model::REPEATING  => \OpenDialogAi\Core\Attribute\BooleanAttribute::class
    ),
];
