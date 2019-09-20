<?php

namespace OpenDialogAi\SensorEngine;

use OpenDialogAi\Core\Utterances\User;

abstract class BaseSensor implements SensorInterface
{
    protected static $name = 'base';

    /**
     * @param string $userId The webchat id of the user
     * @param array $userData Array of user specific data sent with a request
     * @return User
     */
    protected function createUser(string $userId, array $userData): User
    {
        $user = new User($userId);

        isset($userData['first_name']) ? $user->setFirstName($userData['first_name']) : null;
        isset($userData['last_name']) ? $user->setLastName($userData['last_name']) : null;
        isset($userData['email']) ? $user->setEmail($userData['email']) : null;
        isset($userData['external_id']) ? $user->setExternalId($userData['external_id']) : null;
        isset($userData['ipAddress']) ? $user->setIPAddress($userData['ipAddress']) : null;
        isset($userData['country']) ? $user->setCountry($userData['country']) : null;
        isset($userData['browserLanguage']) ? $user->setBrowserLanguage($userData['browserLanguage']) : null;
        isset($userData['os']) ? $user->setOS($userData['os']) : null;
        isset($userData['browser']) ? $user->setBrowser($userData['browser']) : null;
        isset($userData['timezone']) ? $user->setTimezone($userData['timezone']) : null;
        isset($userData['custom']) ? $user->setCustomParameters($userData['custom']) : null;

        return $user;
    }
}
