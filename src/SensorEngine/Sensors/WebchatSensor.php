<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Exceptions\UtteranceUnknownMessageType;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\SensorEngine\BaseSensor;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTriggerUtterance;

class WebchatSensor extends BaseSensor
{
    protected static $name = 'sensor.core.webchat';

    /**
     * Interpret a request.
     *
     * @param Request $request
     * @return UtteranceInterface
     * @throws UtteranceUnknownMessageType
     * @throws FieldNotSupported
     */
    public function interpret(Request $request) : UtteranceInterface
    {
        Log::debug('Interpreting webchat request.');

        switch ($request['content']['type']) {
            case 'chat_open':
                Log::debug('Received webchat open request.');
                $utterance = new WebchatChatOpenUtterance();
                $utterance->setCallbackId($request['content']['data']['callback_id']);
                $utterance->setUserId($request['user_id']);
                return $utterance;
                break;

            case 'text':
                Log::debug('Received webchat message.');
                $utterance = new WebchatTextUtterance();
                $utterance->setText($request['content']['data']['text']);
                $utterance->setUserId($request['user_id']);
                return $utterance;
                break;

            case 'trigger':
                Log::debug('Received webchat trigger message.');
                $utterance = new WebchatTriggerUtterance();
                $utterance->setCallbackId($request['content']['data']['callback_id']);
                $utterance->setUserId($request['user_id']);
                if (isset($request['content']['data']['value'])) {
                    $utterance->setValue($request['content']['data']['value']);
                }
                return $utterance;
                break;

            default:
                Log::debug("Received unknown webchat message type {$request['content']['type']}.");
                throw new UtteranceUnknownMessageType('Unknown Webchat Message Type.');
                break;
        }
    }
}
