<?php

use OpenDialogAi\ContextEngine\Contexts\Custom\MessageHistoryContext;
use OpenDialogAi\Core\Attribute\StringAttribute;

return [

    /**
     * Register your application specific attributes here. They should be registered with:
     * {attribute_name} => Fully/Qualified/ClassName
     *
     * Where ClassName is an implementation of @see \OpenDialogAi\Core\Attribute\AttributeInterface
     */
    'custom_attributes' => [
        'all' => StringAttribute::class,
    ],

    /**
     * Register your custom contexts here. Custom contexts must extend
     * @see \OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext
     *
     * Custom contexts are used to make available application specific attributes that are externally managed
     */
    'custom_contexts' => [
        MessageHistoryContext::class,
    ]
];
