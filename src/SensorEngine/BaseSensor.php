<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\Core\Traits\HasName;

abstract class BaseSensor implements SensorInterface
{
    use HasName;

    static $name = 'base';
}
