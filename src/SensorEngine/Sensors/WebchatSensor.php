<?php

namespace OpenDialogAi\SensorEngine\Sensors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\Core\SensorEngine\Exceptions\UtteranceUnknownMessageType;
use OpenDialogAi\SensorEngine\BaseSensor;

class WebchatSensor extends BaseSensor
{
    public static $name = 'sensor.core.webchat';

    /**
     * Interpret a request.
     *
     * @param Request $request
     * @return UtteranceAttribute
     */
    public function interpret(Request $request) : UtteranceAttribute
    {
        Log::debug('Interpreting webchat request.');

        $content = $request['content'];
        $utterance = new UtteranceAttribute('utterance');
        $utterance->setUtteranceAttribute('utterance_platform', 'webchat');

        switch ($content['type']) {
            case 'chat_open':
                Log::debug('Received webchat open request.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'chat_open')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('callback_id', $content['callback_id'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute('utterance_data', $content['data']['value']);
                }

                return $utterance;
                break;

            case 'text':
                Log::debug('Received webchat message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_message')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('utterance_text', $content['data']['text'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                return $utterance;
                break;

            case 'trigger':
                Log::debug('Received webchat trigger message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_trigger')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('callback_id', $content['callback_id'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute('utterance_data', $content['data']['value']);
                }

                return $utterance;
                break;

            case 'button_response':
                Log::debug('Received webchat button_response message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_button_response')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('callback_id', $content['callback_id'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute('utterance_data', $content['data']['value']);
                }

                return $utterance;
                break;

            case 'url_click':
                Log::debug('Received webchat url_click message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_click')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }

                return $utterance;
                break;

            case 'longtext_response':
                Log::debug('Received webchat longtext_response message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_longtext_response')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }

                return $utterance;
                break;

            case 'form_response':
                Log::debug('Received webchat form_response message.');
                $utterance
                    ->setUtteranceAttribute('utterance_type', 'webchat_form_response')
                    ->setUtteranceAttribute('utterance_data', $content['data'])
                    ->setUtteranceAttribute('callback_id', $content['callback_id'])
                    ->setUtteranceAttribute('utterance_user_id', $request['user_id'])
                    ->setFormValues($content['data']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }

                return $utterance;
                break;

            default:
                Log::debug("Received unknown webchat message type {$content['type']}.");
                throw new UtteranceUnknownMessageType('Unknown Webchat Message Type.');
                break;
        }
    }

    /**
     * @param string $userId The webchat id of the user
     * @param array $userData Array of user specific data sent with a request
     * @return UserAttribute
     */
    protected function createUser(string $userId, array $userData): UserAttribute
    {
        $user = new UserAttribute($userId);
        isset($userData['first_name']) ? $user->setUserAttribute('first_name', $userData['first_name']) : null;
        isset($userData['last_name']) ? $user->setUserAttribute('last_name', $userData['last_name']) : null;
        isset($userData['email']) ? $user->setUserAttribute('email', $userData['email']) : null;
        isset($userData['external_id']) ? $user->setUserAttribute('external_id', $userData['external_id']) : null;
        isset($userData['ipAddress']) ? $user->setUserAttribute('ipAddress', $userData['ipAddress']) : null;
        isset($userData['browserLanguage']) ? $user->setUserAttribute('browserLanguage', $userData['browserLanguage']) : null;
        isset($userData['os']) ? $user->setUserAttribute('os', $userData['os']) : null;
        isset($userData['browser']) ? $user->setUserAttribute('browser', $userData['browser']) : null;
        isset($userData['timezone']) ? $user->setUserAttribute('timezone', $userData['timezone']) : null;
        isset($userData['custom']) ? $user->setUserAttribute('custom', $userData['custom']) : null;


        return $user;
    }
}
