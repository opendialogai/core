<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Traits\HasName;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Exceptions\UtteranceUnknownMessageType;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\Core\Utterances\Webchat\WebchatButtonResponseUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatFormResponseUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatLongtextResponseUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTriggerUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;
use OpenDialogAi\SensorEngine\BaseSensor;

class WebchatSensor extends BaseSensor
{
    use HasName;

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

        $content = $request['content'];
        switch ($content['type']) {
            case 'chat_open':
                Log::debug('Received webchat open request.');
                $utterance = new WebchatChatOpenUtterance();
                $utterance->setData($content['data']);
                $utterance->setCallbackId($content['callback_id']);
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                return $utterance;
                break;

            case 'text':
                Log::debug('Received webchat message.');
                $utterance = new WebchatTextUtterance();
                $utterance->setData($content['data']);
                $utterance->setText($content['data']['text']);
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                return $utterance;
                break;

            case 'trigger':
                Log::debug('Received webchat trigger message.');
                $utterance = new WebchatTriggerUtterance();
                $utterance->setData($content['data']);
                $utterance->setCallbackId($content['callback_id']);
                Log::debug(sprintf('Set callback id as %s', $utterance->getCallbackId()));
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                if (isset($content['data']['value'])) {
                    $utterance->setValue($content['data']['value']);
                }
                return $utterance;
                break;

            case 'button_response':
                Log::debug('Received webchat button_response message.');
                $utterance = new WebchatButtonResponseUtterance();
                $utterance->setData($content['data']);
                $utterance->setCallbackId($content['callback_id']);
                Log::debug(sprintf('Set callback id as %s', $utterance->getCallbackId()));
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                if (isset($content['data']['value'])) {
                    $utterance->setValue($content['data']['value']);
                }
                return $utterance;
                break;

            case 'url_click':
                Log::debug('Received webchat url_click message.');
                $utterance = new WebchatUrlClickUtterance();
                $utterance->setData($content['data']);
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                return $utterance;
                break;

            case 'longtext_response':
                Log::debug('Received webchat longtext_response message.');
                $utterance = new WebchatLongtextResponseUtterance();
                $utterance->setData($content['data']);
                $utterance->setUserId($request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                return $utterance;
                break;

            case 'form_response':
                Log::debug('Received webchat form_response message.');
                $utterance = new WebchatFormResponseUtterance();
                $utterance->setData($content['data']);
                $utterance->setUserId($request['user_id']);
                $utterance->setCallbackId($content['callback_id']);
                $utterance->setFormValues($content['data']);

                if (isset($content['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $content['user']));
                }
                return $utterance;
                break;

            default:
                Log::debug("Received unknown webchat message type {$content['type']}.");
                throw new UtteranceUnknownMessageType('Unknown Webchat Message Type.');
                break;
        }
    }
}
