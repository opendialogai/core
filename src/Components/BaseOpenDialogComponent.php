<?php


namespace OpenDialogAi\Core\Components;

use OpenDialogAi\Core\Exceptions\NameNotSetException;
use OpenDialogAi\Core\Traits\HasName;

abstract class BaseOpenDialogComponent implements OpenDialogComponentInterface
{
    /*
        We're using the pre-existing HasName trait here to get our component ID. Our $componentName is different to the
        name provided by this trait, as $componentName represents a human-readable name for the component.
        TODO: Standardise HasName into the concept of an OpenDialog component.
    */
    use HasName;

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

    /* Component Types */
    const CORE_COMPONENT_SOURCE = 'core';
    const APP_COMPONENT_SOURCE = 'app';
    const CUSTOM_COMPONENT_SOURCE = 'custom';
    const VALID_COMPONENT_SOURCES = [
        self::CORE_COMPONENT_SOURCE,
        self::APP_COMPONENT_SOURCE,
        self::CUSTOM_COMPONENT_SOURCE,
    ];

    protected static ?string $componentName = null;
    protected static ?string $componentDescription = null;
    protected static string $componentType = '';
    protected static string $componentSource = '';

    /**
     * @inheritDoc
     */
    final public static function getComponentData(): OpenDialogComponentData
    {
        return new OpenDialogComponentData(
            static::getComponentType(),
            static::getComponentSource(),
            static::getComponentId(),
            static::getComponentName(),
            static::getComponentDescription()
        );
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentName(): ?string
    {
        return static::$componentName;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentDescription(): ?string
    {
        return static::$componentDescription;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentType(): string
    {
        if (self::$componentType === static::$componentType) {
            throw new MissingRequiredComponentDataException('type');
        }

        if (!in_array(static::$componentType, self::VALID_COMPONENT_TYPES)) {
            throw new InvalidComponentDataException('type', static::$componentType);
        }

        return static::$componentType;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentSource(): string
    {
        if (self::$componentSource === static::$componentSource) {
            throw new MissingRequiredComponentDataException('source');
        }

        if (!in_array(static::$componentSource, self::VALID_COMPONENT_SOURCES)) {
            throw new InvalidComponentDataException('source', static::$componentSource);
        }

        return static::$componentSource;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentId(): string
    {
        try {
            return static::getName();
        } catch (NameNotSetException $e) {
            throw new MissingRequiredComponentDataException('id');
        }
    }
}
