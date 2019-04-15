<?php

return [
    'supported_attributes' => [
        'name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'first_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'last_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'full_name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'external_id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'email' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'ei_type' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'timestamp' => \OpenDialogAi\Core\Attribute\IntAttribute::class,

        // Intents
        \OpenDialogAi\Core\Conversation\Model::CONFIDENCE => \OpenDialogAi\Core\Attribute\FloatAttribute::class
    ],
];
