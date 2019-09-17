<?php

namespace OpenDialogAi\Core\SensorEngine\tests\Sensors;

use Illuminate\Http\Request;
use OpenDialogAi\Core\Traits\HasName;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\SensorEngine\BaseSensor;

class DummySensor extends BaseSensor
{
    use HasName;
    
    public static $name = 'badly_formed';

    public function interpret(Request $request): UtteranceInterface
    {
        // TODO: Implement interpret() method.
    }
}
