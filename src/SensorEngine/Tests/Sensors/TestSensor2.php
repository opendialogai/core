<?php

namespace OpenDialogAi\Core\SensorEngine\Tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\SensorEngine\BaseSensor;

// Same name as test sensor
class TestSensor2 extends BaseSensor
{
    public static ?string $componentId = 'sensor.core.test';

    public function interpret(Request $request): UtteranceAttribute
    {
        // TODO: Implement interpret() method.
    }
}
