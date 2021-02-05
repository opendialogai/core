<?php

namespace OpenDialogAi\Core\SensorEngine\Tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\SensorEngine\BaseSensor;

class DummySensor extends BaseSensor
{
    public static $name = 'badly_formed';

    public function interpret(Request $request): UtteranceAttribute
    {
        // TODO: Implement interpret() method.
    }
}
