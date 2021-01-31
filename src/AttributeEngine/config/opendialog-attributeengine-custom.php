<?php

return [
    /**
     * Register your application specific attribute types here. They should be registered as:
     * [
     *   Fully/Qualified/ClassName,
     *   Fully/Qualified/ClassName2,
     *   ...
     * ]
     *
     * Where ClassName is an implementation of @see \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface
     */
    'custom_attribute_types' => [
//        \OpenDialogAi\AttributeEngine\Tests\ExampleCustomAttributeType::class
    ],

    /**
     * Register your application specific attributes here. They should be registered with:
     * {attribute_name} => Fully/Qualified/ClassName
     *
     * Where ClassName is an implementation of @see \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface
     */
    'custom_attributes' => [
        // 'attribute_name' => AttributeTypeClass::class
    ],
];
