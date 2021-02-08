<?php

namespace OpenDialogAi\Core\Tests\Utils;

use Faker\Factory;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;


/**
 * Static methods to help generating utterances to use in tests
 */
class UtteranceGenerator
{
    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param UserAttribute $user
     * @return UtteranceAttribute
     */
    public static function generateChatOpenUtterance($callbackId, User $user = null): UtteranceAttribute
    {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new UtteranceAttribute(UtteranceAttribute::UTTERANCE_USER);
        $utterance->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::CHAT_OPEN)
            ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $callbackId)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER, $user)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $user->getId());
        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $text
     * @param UserAttribute $user
     * @return UtteranceAttribute
     */
    public static function generateTextUtterance($text = '', $user = null): UtteranceAttribute
    {
        if ($user === null) {
            $user = self::generateUser();
        }


        $utterance = new UtteranceAttribute(UtteranceAttribute::UTTERANCE_USER);
        $utterance->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_PLATFORM, UtteranceAttribute::WEBCHAT_PLATFORM)
            ->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_MESSAGE)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_TEXT, $text)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER, $user)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $user->getId());
        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param $value
     * @param UserAttribute $user
     * @return UtteranceAttribute
     */
    public static function generateButtonResponseUtterance(
        $callbackId = '',
        $value = '',
        $user = null
    ): UtteranceAttribute {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new UtteranceAttribute(UtteranceAttribute::UTTERANCE_USER);
        $utterance->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_BUTTON_RESPONSE)
            ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $callbackId)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_DATA_VALUE, $value)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER, $user)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $user->getId());

        return $utterance;
    }

    /**
     * If no user is passed in, a random user is generated
     *
     * @param $callbackId
     * @param $formValues
     * @param UserAttribute $user
     * @return UtteranceAttribute
     */
    public static function generateFormResponseUtterance(
        $callbackId,
        $formValues,
        $user = null
    ): UtteranceAttribute {
        if ($user === null) {
            $user = self::generateUser();
        }

        $utterance = new UtteranceAttribute(UtteranceAttribute::UTTERANCE_USER);
        $utterance->setUtteranceAttribute(UtteranceAttribute::TYPE, UtteranceAttribute::WEBCHAT_FORM_RESPONSE)
            ->setUtteranceAttribute(UtteranceAttribute::CALLBACK_ID, $callbackId)
            ->setFormValues($formValues)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER, $user)
            ->setUtteranceAttribute(UtteranceAttribute::UTTERANCE_USER_ID, $user->getId());
        return $utterance;
    }

    /**
     * @return UserAttribute
     */
    public static function generateUser(): UserAttribute
    {
        $generator = Factory::create();

        $user = new UserAttribute(UtteranceAttribute::UTTERANCE_USER);
        $user->setUserId($generator->uuid);
        $user->setFirstName($generator->firstName);
        $user->setLastName($generator->lastName);
        return $user;
    }
}
