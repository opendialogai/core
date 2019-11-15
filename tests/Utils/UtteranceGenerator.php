<?php

namespace OpenDialogAi\Core\Tests\Utils;

use Faker\Factory;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\Webchat\WebchatButtonResponseUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatFormResponseUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;

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
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new WebchatChatOpenUtterance();
        try {
            $utterance->setCallbackId($callbackId);
            $utterance->setUser($user);
            $utterance->setUserId($user->getId());
        } catch (FieldNotSupported $e) {
            //
        }

        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $text
     * @param User $user
     * @return WebchatTextUtterance
     */
    public static function generateTextUtterance($text = '', $user = null): WebchatTextUtterance
    {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new WebchatTextUtterance();
        try {
            $utterance->setText($text);
            $utterance->setUser($user);
            $utterance->setUserId($user->getId());
        } catch (FieldNotSupported $e) {
            //
        }

        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param $value
     * @param User $user
     * @return WebchatButtonResponseUtterance
     */
    public static function generateButtonResponseUtterance(
        $callbackId = '',
        $value = '',
        $user = null
    ): WebchatButtonResponseUtterance {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new WebchatButtonResponseUtterance();
        try {
            $utterance->setCallbackId($callbackId);
            $utterance->setValue($value);
            $utterance->setUser($user);
            $utterance->setUserId($user->getId());
        } catch (FieldNotSupported $e) {
            //
        }

        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param $formValues
     * @param User $user
     * @return WebchatFormResponseUtterance
     */
    public static function generateFormResponseUtterance(
        $callbackId,
        $formValues,
        $user = null
    ): WebchatFormResponseUtterance {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new WebchatFormResponseUtterance();
        try {
            $utterance->setCallbackId($callbackId);
            $utterance->setFormValues($formValues);
            $utterance->setUser($user);
            $utterance->setUserId($user->getId());
        } catch (FieldNotSupported $e) {
            //
        }

        return $utterance;
    }

    /**
     * @return User
     */
    public static function generateUser(): User
    {
        $generator = Factory::create();

        $user = new User($generator->uuid);
        $user->setFirstName($generator->firstName);
        $user->setLastName($generator->lastName);
        return $user;
    }
}
