<?php

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\TimestampAttribute;
use OpenDialogAi\Core\Conversation\Model;

return [
    'supported_attributes' => [
        'attribute_name'   => StringAttribute::class,
        'attribute_value' => StringAttribute::class,
        'callback_value' => StringAttribute::class,
        'context' => StringAttribute::class,
        'ei_type' => StringAttribute::class,
        'email' => StringAttribute::class,
        'external_id' => StringAttribute::class,
        'first_name' => StringAttribute::class,
        'full_name' => StringAttribute::class,
        'id' => StringAttribute::class,
        'last_name' => StringAttribute::class,
        'age' => IntAttribute::class,
        'name' => StringAttribute::class,
        'operation' => StringAttribute::class,
        'timestamp' => IntAttribute::class,
        'last_seen' => TimestampAttribute::class,
        'first_seen' => TimestampAttribute::class,
        'all' => StringAttribute::class,
        'attributes' => ArrayAttribute::class,
        'parameters' => ArrayAttribute::class,

        'current_conversation' => StringAttribute::class,
        'current_scene' => StringAttribute::class,
        'current_intent' => StringAttribute::class,
        'interpreted_intent' => StringAttribute::class,
        'next_intents' => ArrayAttribute::class,

        Model::CONVERSATION_STATUS => StringAttribute::class,
        Model::CONVERSATION_VERSION => IntAttribute::class,

        Model::USER_ATTRIBUTE_TYPE => StringAttribute::class,
        Model::USER_ATTRIBUTE_VALUE => StringAttribute::class,

        // Intents
        Model::ORDER => IntAttribute::class,
        Model::CONFIDENCE => FloatAttribute::class,
        Model::COMPLETES => BooleanAttribute::class,
        Model::REPEATING  => BooleanAttribute::class
    ],
];
