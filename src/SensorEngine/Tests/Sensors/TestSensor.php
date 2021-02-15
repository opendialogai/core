<?php

namespace OpenDialogAi\Core\SensorEngine\Tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\SensorEngine\BaseSensor;

class TestSensor extends BaseSensor
{
    public static $name = 'sensor.core.test';

    public function interpret(Request $request): UtteranceAttribute
    {
        // TODO: Implement interpret() method.
    }
}
