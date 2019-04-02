<?php

namespace OpenDialogAi\SensorEngine\Sensors;

abstract class BaseSensor implements SensorInterface
{
    private $name;

    public function getName() : string
    {
        return $this->name;
    }
}
