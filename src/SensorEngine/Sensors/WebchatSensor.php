<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Exceptions\UtteranceUnknownMessageType;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTriggerUtterance;
use OpenDialogAi\SensorEngine\BaseSensor;

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
                $utterance->setData($request['content']['data']);
                $utterance->setCallbackId($request['content']['data']['callback_id']);
                $utterance->setUserId($request['user_id']);
                if (isset($request['content']['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $request['content']['user']));
                }
                return $utterance;
                break;

            case 'text':
                Log::debug('Received webchat message.');
                $utterance = new WebchatTextUtterance();
                $utterance->setData($request['content']['data']);
                $utterance->setText($request['content']['data']['text']);
                $utterance->setUserId($request['user_id']);
                if (isset($request['content']['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $request['content']['user']));
                }
                return $utterance;
                break;

            case 'trigger':
                Log::debug('Received webchat trigger message.');
                $utterance = new WebchatTriggerUtterance();
                $utterance->setData($request['content']['data']);
                $utterance->setCallbackId($request['content']['data']['callback_id']);
                Log::debug(sprintf('Set callback id as %s', $utterance->getCallbackId()));
                $utterance->setUserId($request['user_id']);
                if (isset($request['content']['user'])) {
                    $utterance->setUser($this->createUser($request['user_id'], $request['content']['user']));
                }
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

    /**
     * @param string $userId The webchat id of the user
     * @param array $userData Array of user specific data sent with a request
     * @return User
     */
    private function createUser(string $userId, array $userData): User
    {
        $user = new User($userId);

        isset($userData['first_name']) ? $user->setFirstName($userData['first_name']) : null;
        isset($userData['last_name']) ? $user->setLastName($userData['last_name']) : null;
        isset($userData['email']) ? $user->setEmail($userData['email']) : null;
        isset($userData['external_id']) ? $user->setExternalId($userData['external_id']) : null;
        isset($userData['custom']) ? $user->setCustomParameters($userData['custom']) : null;

        return $user;
    }
}
