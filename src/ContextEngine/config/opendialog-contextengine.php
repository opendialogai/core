<?php

return [
    'supported_attributes' => [
        'user.name' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'user.id' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'user.ei_type' => \OpenDialogAi\Core\Attribute\StringAttribute::class,
        'user.timestamp' => \OpenDialogAi\Core\Attribute\IntAttribute::class,

        // Intents
        \OpenDialogAi\Core\Conversation\Intent::CONFIDENCE => \OpenDialogAi\Core\Attribute\FloatAttribute::class
    ],
];
