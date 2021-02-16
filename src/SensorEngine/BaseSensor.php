<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;

abstract class BaseSensor implements SensorInterface, OpenDialogComponent
{
    use ODComponent;

    protected static string $componentType = ODComponentTypes::SENSOR_COMPONENT_TYPE;
    protected static string $componentSource = ODComponentTypes::APP_COMPONENT_SOURCE;
}
