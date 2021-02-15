<?php


namespace OpenDialogAi\ContextEngine\Contexts\User;


use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserHistoryRecord;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\UnableToLoadAttributeFromPersistentStorageException;

class UserDataClient implements ContextDataClient
{
    public function __construct()
    {
    }

    public function loadAttribute(string $attributeName): Attribute
    {
        Log::debug(sprintf("Cannot load attribute with name %s - from persistent storage", $attributeName));
        throw new UnableToLoadAttributeFromPersistentStorageException(
            sprintf("Cannot load attribute with name %s - from persistent storage", $attributeName));
        // TODO: Implement loadAttribute() method.
        // To load the user attribute we should retreive the "user_utterance" from the user context
        // and extract the "user_id" - that can then be fed into the createOrUpdate function here.
    }

    public function loadAttributes(array $attributes): Map
    {
        // TODO: Implement loadAttributes() method.
    }

    public function persistAttribute(string $attributeName, string $context): bool
    {
        // TODO: Implement persistAttribute() method.
    }

    public function persistAttributes(array $attributes, string $context): bool
    {
        // TODO: Implement persistAttributes() method.
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
