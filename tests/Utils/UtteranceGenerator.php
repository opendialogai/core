<?php

namespace OpenDialogAi\Core\Tests\Utils;

use Faker\Factory;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;

/**
 * Static methods to help generating utterances to use in tests
 */
class UtteranceGenerator
{
    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param User $user
     * @return WebchatChatOpenUtterance
     */
    public static function generateChatOpenUtterance($callbackId, User $user = null): WebchatChatOpenUtterance
    {
        $generator = Factory::create();

        if ($user === null) {
            $user = new User($generator->uuid);
            $user->setFirstName($generator->firstName);
            $user->setLastName($generator->lastName);
        }

        try {
            $utterance = new WebchatChatOpenUtterance();
            $utterance->setCallbackId($callbackId);
            $utterance->setUser($user);
            $utterance->setUserId($user->getId());
        } catch (FieldNotSupported $e) {
            //
        }

        return $utterance;
    }
}
