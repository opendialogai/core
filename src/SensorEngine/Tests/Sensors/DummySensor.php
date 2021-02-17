<?php

namespace OpenDialogAi\Core\SensorEngine\Tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\SensorEngine\BaseSensor;

class DummySensor extends BaseSensor
{
    protected static string $componentId = 'badly_formed';

    protected static ?string $componentName = 'Example sensor';
    protected static ?string $componentDescription = 'Just an example sensor.';

    public function interpret(Request $request): UtteranceAttribute
    {
        // TODO: Implement interpret() method.
    }
}
