<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\SensorEngine\Exceptions\SensorNameNotSetException;

abstract class BaseSensor implements SensorInterface
{
    protected static $name = 'base';
}
