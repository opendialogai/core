<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\SensorEngine\Exceptions\SensorNameNotSetException;

abstract class BaseSensor implements SensorInterface
{
    protected static $name = 'base';

    /**
     * @inheritDoc
     */
    public static function getName() : string
    {
        if (static::$name === self::$name) {
            throw new SensorNameNotSetException(sprintf("Sensor %s has not defined a name", __CLASS__));
        }
        return static::$name;
    }
}
