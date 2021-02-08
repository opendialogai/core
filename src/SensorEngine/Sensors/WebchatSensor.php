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
        $utterance->setPlatform(UtteranceAttribute::WEBCHAT_PLATFORM);

        switch ($content['type']) {
            case UtteranceAttribute::CHAT_OPEN:
                Log::debug('Received webchat open request.');
                $utterance
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::CHAT_OPEN)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $content['callback_id'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA_VALUE, $content['data']['value']);
                }

                return $utterance;
                break;

            case 'text':
                Log::debug('Received webchat message.');
                $utterance
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_MESSAGE)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_TEXT, $content['data']['text'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
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
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_TRIGGER)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $content['callback_id'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA_VALUE, $content['data']['value']);
                }

                return $utterance;
                break;

            case 'button_response':
                Log::debug('Received webchat button_response message.');
                $utterance
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_BUTTON_RESPONSE)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $content['callback_id'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
                if (isset($content['user'])) {
                    $utterance->setUtteranceAttribute(
                        'utterance_user',
                        $this->createUser($request['user_id'], $content['user'])
                    );
                }
                if (isset($content['data']['value'])) {
                    $utterance->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA_VALUE, $content['data']['value']);
                }

                return $utterance;
                break;

            case 'url_click':
                Log::debug('Received webchat url_click message.');
                $utterance
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_CLICK)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
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
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_LONGTEXT_RESPONSE)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id']);
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
                    ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_FORM_RESPONSE)
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA, $content['data'])
                    ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $content['callback_id'])
                    ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $request['user_id'])
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
        $user = new UserAttribute(UtteranceAttribute::UTTERANCE_USER);
        isset($userData[UserAttribute::FIRST_NAME]) ? $user->setUserAttribute('first_name', $userData['first_name']) : null;
        isset($userData[UserAttribute::LAST_NAME]) ? $user->setUserAttribute('last_name', $userData['last_name']) : null;
        isset($userData[UserAttribute::EMAIL]) ? $user->setUserAttribute('email', $userData['email']) : null;
        isset($userData[UserAttribute::EXTERNAL_ID]) ? $user->setUserAttribute('external_id', $userData['external_id']) : null;
        isset($userData[UserAttribute::IP_ADDRESS]) ? $user->setUserAttribute('ipAddress', $userData['ipAddress']) : null;
        isset($userData[UserAttribute::COUNTRY]) ? $user->setUserAttribute('country', $userData['country']) : null;
        isset($userData[UserAttribute::BROWSER_LANGUAGE]) ?
            $user->setUserAttribute('browserLanguage', $userData['browserLanguage']) : null;
        isset($userData[UserAttribute::OS]) ? $user->setUserAttribute('os', $userData['os']) : null;
        isset($userData[UserAttribute::BROWSER]) ? $user->setUserAttribute('browser', $userData['browser']) : null;
        isset($userData[UserAttribute::TIMEZONE]) ? $user->setUserAttribute('timezone', $userData['timezone']) : null;
        isset($userData[UserAttribute::CUSTOM]) ? $user->setUserAttribute(UserAttribute::CUSTOM, $userData['custom']) : null;


        return $user;
    }
}
