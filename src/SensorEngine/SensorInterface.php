<?php

namespace OpenDialogAi\SensorEngine;

use Illuminate\Http\Request;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\SensorEngine\Exceptions\SensorNameNotSetException;

/**
 * Definition of a Sensor
 */
interface SensorInterface
{
    /**
     * Interprets a request and returns an Utterance for the platform and message type
     *
     * @param Request $request
     * @return UtteranceInterface
     */
    public function interpret(Request $request) : UtteranceInterface;

    /**
     * Gets the name of the Sensor
     *
     * @return string
     * @throws SensorNameNotSetException
     */
    public static function getName(): string;
}
