<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\Core\Traits\HasName;

abstract class BaseSensor implements SensorInterface
{
    use HasName;

    public static $name = 'base';
}
