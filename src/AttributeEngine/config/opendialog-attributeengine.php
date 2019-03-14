<?php

return [
    'available_attributes' => [
        'attributes.core.userName' => \OpenDialogAi\AttributeEngine\Attributes\UserName::class
    ],

    'available_contexts' => [
        'attributes.core.currentUserContext' => \OpenDialogAi\AttributeEngine\Contexts\CurrentUserContext::class
    ],
];
