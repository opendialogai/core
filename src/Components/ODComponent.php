<?php


namespace OpenDialogAi\Core\Components;

use OpenDialogAi\Core\Components\Contracts\OpenDialogComponentData;
use OpenDialogAi\Core\Components\Exceptions\InvalidComponentDataException;
use OpenDialogAi\Core\Components\Exceptions\MissingRequiredComponentDataException;
use OpenDialogAi\Core\Exceptions\NameNotSetException;

trait ODComponent
{
    protected static ?string $componentName = null;
    protected static ?string $componentDescription = null;
    protected static ?string $componentId = null;

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
        if (!in_array(static::$componentType, ODComponentTypes::VALID_COMPONENT_TYPES)) {
            throw new InvalidComponentDataException('type', static::$componentType);
        }

        return static::$componentType;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentSource(): string
    {
        if (!in_array(static::$componentSource, ODComponentTypes::VALID_COMPONENT_SOURCES)) {
            throw new InvalidComponentDataException('source', static::$componentSource);
        }

        return static::$componentSource;
    }

    /**
     * @inheritDoc
     */
    final public static function getComponentId(): string
    {
        if (static::$componentId === self::$componentId) {
            throw new MissingRequiredComponentDataException('component_id');
        }
        return static::$componentId;
    }

}
