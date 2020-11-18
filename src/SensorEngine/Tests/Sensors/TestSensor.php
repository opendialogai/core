<?php

namespace OpenDialogAi\Core\SensorEngine\Tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\SensorEngine\BaseSensor;

class TestSensor extends BaseSensor
{
    public static $name = 'sensor.core.test';

    public function interpret(Request $request): UtteranceInterface
    {
        // TODO: Implement interpret() method.
    }
}
