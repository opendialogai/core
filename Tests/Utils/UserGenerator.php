<?php


namespace OpenDialogAi\Core\Tests\Utils;


use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;

class UserGenerator
{
    public static function generateUtteranceUserWithCustomAttributes()
    {
        $user = new UserAttribute(UtteranceAttribute::UTTERANCE_USER);
        $user->setUserId('SP-937-215')
            ->setFirstName('Jean-Luc')
            ->setLastName('Picard')
            ->setCountry('Starfleet');

        return $user;
    }

    public static function generateCurrentUserWithCustomAttributes()
    {
        $user = new UserAttribute(UserAttribute::CURRENT_USER);
        $user->setUserId('SP-937-215')
            ->setFirstName('Jean-Luc')
            ->setLastName('Picard')
            ->setCountry('Starfleet');

        return $user;
    }
}
