<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;

class WebchatSensor extends BaseSensor implements SensorInterface
{
    protected $name = 'webchat';

    /**
     * Interpret a request.
     *
     * @return UtteranceInterface
     */
    public function interpret(object $request) : UtteranceInterface
    {
        \Log::debug('Interpreting webchat request.');
        return new WebchatChatOpenUtterance();
    }
}
