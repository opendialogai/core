<?php

return [
    'supported_attributes' => [
        'name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'ei_type' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'timestamp' => \OpenDialogAi\Core\Attribute\IntAttribute::class,

        // Intents
        \OpenDialogAi\Core\Conversation\Intent::CONFIDENCE => \OpenDialogAi\Core\Attribute\FloatAttribute::class
    ],
];
