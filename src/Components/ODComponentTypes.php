<?php


namespace OpenDialogAi\Core\Components;

class ODComponentTypes
{
    /* Component Types */
    const ACTION_COMPONENT_TYPE = 'action';
    const ATTRIBUTE_COMPONENT_TYPE = 'attribute';
    const ATTRIBUTE_TYPE_COMPONENT_TYPE = 'attribute_type';
    const CONTEXT_COMPONENT_TYPE = 'context';
    const INTERPRETER_COMPONENT_TYPE = 'interpreter';
    const OPERATION_COMPONENT_TYPE = 'operation';
    const FORMATTER_COMPONENT_TYPE = 'formatter';
    const SENSOR_COMPONENT_TYPE = 'sensor';
    const VALID_COMPONENT_TYPES = [
        self::ACTION_COMPONENT_TYPE,
        self::ATTRIBUTE_COMPONENT_TYPE,
        self::ATTRIBUTE_TYPE_COMPONENT_TYPE,
        self::CONTEXT_COMPONENT_TYPE,
        self::INTERPRETER_COMPONENT_TYPE,
        self::OPERATION_COMPONENT_TYPE,
        self::FORMATTER_COMPONENT_TYPE,
        self::SENSOR_COMPONENT_TYPE,
    ];

    /* Component Sources */
    const CORE_COMPONENT_SOURCE = 'core';
    const APP_COMPONENT_SOURCE = 'app';
    const CUSTOM_COMPONENT_SOURCE = 'custom';
    const VALID_COMPONENT_SOURCES = [
        self::CORE_COMPONENT_SOURCE,
        self::APP_COMPONENT_SOURCE,
        self::CUSTOM_COMPONENT_SOURCE,
    ];

}
