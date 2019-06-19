<?php

return [
    'supported_attributes' => [
        'attribute_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'attribute_value' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'callback_value' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'context' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'ei_type' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'email' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'external_id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'first_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'full_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'last_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'operation' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'timestamp' => \OpenDialogAi\Core\Attribute\IntAttribute::class,
        'last_seen' => OpenDialogAi\Core\Attribute\TimestampAttribute::class,
        'first_seen' => OpenDialogAi\Core\Attribute\TimestampAttribute::class,

        'qna_answer' => \OpenDialogAi\Core\Attribute\StringAttribute::class,

        'conversation.currentConversation' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'conversation.currentScene' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'conversation.currentIntent' => \OpenDialogAi\Core\Attribute\StringAttribute::class,

        // Intents
        \OpenDialogAi\Core\Conversation\Model::CONFIDENCE => \OpenDialogAi\Core\Attribute\FloatAttribute::class
    ],
];
