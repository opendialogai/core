<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use OpenDialogAi\Core\Utterances\UtteranceInterface;

/**
 * This is a placeholder interface for what a sensor needs to do
 */
interface SensorInterface
{
    public function interpret(object $request) : UtteranceInterface;
}
