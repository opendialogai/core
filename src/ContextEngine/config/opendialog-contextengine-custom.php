<?php

return [

    /**
     * Register your application specific attributes here. They should be registered with:
     * {attribute_name} => Fully/Qualified/ClassName
     *
     * Where ClassName is an implementation of @see \OpenDialogAi\Core\Attribute\AttributeInterface
     */
    'custom_attributes' => [
        // 'attribute_name' => AttributeTypeClass::class
    ],

    /**
     * Register your custom contexts here. Custom contexts must extend
     * @see \OpenDialogAi\ContextEngine\Contexts\Custom\AbstractCustomContext
     *
     * Custom contexts are used to make available application specific attributes that are externally managed
     */
    'custom_contexts' => [
//        \OpenDialogAi\ContextEngine\tests\contexts\DummyCustomContext::class
    ]
];
