<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Http\Request;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;

/**
 * Definition of a Sensor
 */
interface SensorInterface
{
    /**
     * Interprets a request and returns an Utterance for the platform and message type
     *
     * @param Request $request
     * @return UtteranceAttribute
     */
    public function interpret(Request $request) : UtteranceAttribute;
}
