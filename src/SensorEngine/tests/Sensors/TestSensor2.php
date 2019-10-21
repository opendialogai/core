<?php

namespace OpenDialogAi\Core\SensorEngine\tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\SensorEngine\BaseSensor;

// Same name as test sensor
class TestSensor2 extends BaseSensor
{
    public static $name = 'sensor.core.test';

    public function interpret(Request $request): UtteranceInterface
    {
        // TODO: Implement interpret() method.
    }
}
