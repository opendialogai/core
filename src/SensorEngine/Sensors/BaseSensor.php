<?php

namespace OpenDialogAi\SensorEngine\Sensors;

abstract class BaseSensor implements SensorInterface
{
    protected $name;

    public function getName() : string
    {
        return $this->name;
    }
}
