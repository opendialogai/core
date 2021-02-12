<?php


namespace OpenDialogAi\ContextEngine\Contexts\User;


use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserHistoryRecord;

class UserDataClient
{
    public function __construct()
    {
    }

    public function createOrUpdate(string $userId, UserAttribute $incomingUser): UserAttribute
    {
        // @todo Check if a user with the id of the $incomingUser exists in persistent storage.
        // If a user exists retrieve that user, update their attributes based on information coming from
        // $incomingUser and persist the changes.

        // Then retrieve that user record and return it as the current user.
        $currentUser = new UserAttribute('current_user');
        $currentUser->setUserId($incomingUser->getUserId());

        // If the user already exists in persistent storage retrieve the UserHistoryRecord and attach it to the user
        $record = new UserHistoryRecord(UserHistoryRecord::USER_HISTORY_RECORD);
        $record->setUserHistoryRecordAttribute(UserHistoryRecord::COMPLETED, true);

        $currentUser->setUserHistoryRecord($record);

        // Once we have a currentUser persist that user to MySQL as well.
        MySqlUserRepository::persistUserToMySql($currentUser);

        return $currentUser;
    }
}
