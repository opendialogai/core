<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\Utterances\User;

class MySqlUserRepository
{
    /**
     * Creates a user in mysql using the @see ChatbotUser model.
     * Uses the user id as the
     *
     * @param User $user
     */
    public static function persistUserToMySql(User $user): void
    {
        ChatbotUser::updateOrCreate(
            [
                'user_id' => $user->getId(),
            ],
            [
                'ip_address' => $user->getIPAddress(),
                'country' => $user->getCountry(),
                'browser_language' => $user->getBrowserLanguage(),
                'os' => $user->getOS(),
                'browser' => $user->getBrowser(),
                'timezone' => $user->getTimezone(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
            ]
        );

    }

}
