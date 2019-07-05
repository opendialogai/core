<?php

namespace OpenDialogAi\ContextEngine\Contexts\User;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotPersistUserRecordException;
use OpenDialogAi\ContextEngine\Exceptions\NoOngoingConversationException;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\UtteranceInterface;

class UserService
{
    /* @var DGraphClient */
    private $dGraphClient;

    /* @var AttributeResolver */
    private $attributeResolver;

    public function __construct(DGraphClient $dGraphClient)
    {
        $this->dGraphClient = $dGraphClient;
    }

    public function setAttributeResolver(AttributeResolver $attributeResolver): void
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * This retrieves the user from dgraph and sets the current conversation
     *
     * @param $userId
     * @return ChatbotUser
     */
    public function getUser($userId): ChatbotUser
    {
        $response = $this->dGraphClient->query($this->getUserQuery($userId));

        $user = new ChatbotUser();
        if (isset($response->getData()[0]['id'])) {
            foreach ($response->getData()[0] as $name => $value) {
                if (in_array($name, [Model::HAVING_CONVERSATION, Model::HAD_CONVERSATION, Model::CURRENT_INTENT])) {
                    continue;
                }

                if ($name === 'id') {
                    $user->setId($value);
                    continue;
                }

                if ($name === 'uid') {
                    $user->setUid($value);
                    continue;
                }

                try {
                    $attribute = $this->attributeResolver->getAttributeFor($name, $value);
                    $user->addAttribute($attribute);
                } catch (AttributeIsNotSupported $e) {
                    Log::warning(sprintf('Attribute for user could not be resolved %s => %s', $name, $value));
                    continue;
                }
            }
        }

        if (isset($response->getData()[0][Model::HAVING_CONVERSATION])) {
            $user->setCurrentConversationUid($response->getData()[0][Model::HAVING_CONVERSATION][0][Model::UID]);
        }

        if (isset($response->getData()[0][Model::HAVING_CONVERSATION][0][Model::CURRENT_INTENT])) {
            $intentId = $response->getData()[0][Model::HAVING_CONVERSATION][0][Model::CURRENT_INTENT][0][Model::UID];
            $user->setCurrentIntentUid($intentId);
        }
        return $user;
    }

    /**
     * @param UtteranceInterface $utterance
     * @return ChatbotUser
     * @throws FieldNotSupported
     */
    public function createOrUpdateUser(UtteranceInterface $utterance): ChatbotUser
    {
        if ($this->userExists($utterance->getUserId())) {
            return $this->updateUserFromUtterance($utterance);
        }

        return $this->createUserFromUtterance($utterance);
    }

    /**
     * @param UtteranceInterface $utterance
     * @return ChatbotUser
     * @throws FieldNotSupported
     */
    public function updateUserFromUtterance(UtteranceInterface $utterance): ChatbotUser
    {
        // We are dealing with an existing user to go get them
        $user = $this->getUser($utterance->getUserId());
        $this->updateFromUtteranceUserObject($utterance->getUser(), $user);

        $chatbotUser = $this->updateUser($user);
        MySqlUserRepository::persistUserToMySql($utterance->getUser());
        return $chatbotUser;
    }

    /**
     * @param User $user
     * @param ChatbotUser $chatbotUser
     */
    protected function updateFromUtteranceUserObject(User $user, ChatbotUser $chatbotUser): void
    {
        if ($user->hasFirstName()) {
            $this->setUserAttribute($chatbotUser, 'first_name', $user->getFirstName());
        }

        if ($user->hasLastName()) {
            $this->setUserAttribute($chatbotUser, 'last_name', $user->getLastName());
        }

        if ($user->hasEmail()) {
            $this->setUserAttribute($chatbotUser, 'email', $user->getEmail());
        }

        if ($user->hasExternalId()) {
            $this->setUserAttribute($chatbotUser, 'external_id', $user->hasExternalId());
        }

        if ($user->hasCustomParameters()) {
            foreach ($user->getCustomParameters() as $key => $value) {
                $this->setUserAttribute($chatbotUser, $key, $value);
            }
        }
    }

    /**
     * @param UtteranceInterface $utterance
     * @return ChatbotUser
     * @throws FieldNotSupported
     */
    public function createUserFromUtterance(UtteranceInterface $utterance): ChatbotUser
    {
        $user = new ChatbotUser($utterance->getUserId());
        $this->updateFromUtteranceUserObject($utterance->getUser(), $user);

        $chatbotUser = $this->updateUser($user);
        MySqlUserRepository::persistUserToMySql($utterance->getUser());

        // Set user 'first_seen' timestamp attribute.
        $this->setUserAttribute($chatbotUser, 'first_seen', now()->timestamp);

        return $chatbotUser;
    }

    /**
     * @param ChatbotUser $user
     * @param Conversation $conversation
     * @return Node
     */
    public function setCurrentConversation(ChatbotUser $user, Conversation $conversation): Node
    {
        $user->setCurrentConversation($conversation);
        return $this->updateUser($user);
    }

    /**
     * @param ChatbotUser $user
     * @return ChatbotUser
     * @throws GuzzleException
     */
    public function moveCurrentConversationToPast(ChatbotUser $user): ChatbotUser
    {
        // Delete the current relationship from Dgraph.
        $this->dGraphClient->deleteRelationship(
            $user->getUid(),
            $user->getCurrentConversationUid(),
            Model::HAVING_CONVERSATION
        );

        $this->dGraphClient->createRelationship(
            $user->getUid(),
            $user->getCurrentConversationUid(),
            Model::HAD_CONVERSATION
        );

        $user->unsetCurrentConversation();
        return $user;
    }

    /**
     * @param ChatbotUser $user
     * @param Intent $intent
     * @return Node
     * @throws GuzzleException
     */
    public function setCurrentIntent(ChatbotUser $user, Intent $intent): Node
    {
        if ($user->hasCurrentIntent()) {
            // From conversation to current intent
            $this->dGraphClient->deleteRelationship(
                $user->getCurrentConversationUid(),
                $user->getCurrentIntentUid(),
                Model::CURRENT_INTENT
            );

            // From user to current intent
            $this->dGraphClient->deleteRelationship(
                $user->getUid(),
                $user->getCurrentIntentUid(),
                Model::CURRENT_INTENT
            );

            // From previous intent to current intent
            $this->dGraphClient->createRelationship(
                $user->getCurrentIntentUid(),
                $intent->getUid(),
                Model::FOLLOWED_BY
            );
        }

        $this->dGraphClient->createRelationship(
            $user->getCurrentConversationUid(),
            $intent->getUid(),
            Model::CURRENT_INTENT
        );

        // Add a relationship from user to current intent directly
        $this->dGraphClient->createRelationship(
            $user->getUid(),
            $intent->getUid(),
            Model::CURRENT_INTENT
        );

        return $user->setCurrentIntentUid($intent->getUid());
    }

    /**
     * Updates the user in DGraph.
     *
     * @param ChatbotUser $user
     * @return ChatbotUser
     * @throws CouldNotPersistUserRecordException
     */
    public function updateUser(ChatbotUser $user): ChatbotUser
    {
        $mutation = new DGraphMutation($user);
        $mutationResponse = $this->dGraphClient->tripleMutation($mutation);

        if ($mutationResponse->isSuccessful()) {
            return $this->getUser($user->getId());
        }

        throw new CouldNotPersistUserRecordException(sprintf("Couldn't persist user to dgraph %s", $user->getId()));
    }

    /**
     * @param $userId
     * @return bool
     */
    public function userExists($userId): bool
    {
        $response = $this->dGraphClient->query($this->getUserQuery($userId));
        if (isset($response->getData()[0]['id'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function userIsHavingConversation($userId): bool
    {
        if (isset($this->getOngoingConversationIdQuery($userId)[0][Model::HAVING_CONVERSATION])) {
            return true;
        }

        return false;
    }

    /**
     * @param $userId
     * @return mixed
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function getCurrentConversation($userId)
    {
        if ($this->userIsHavingConversation($userId)) {
            $conversationUid = $this->getOngoingConversationIdQuery(
                $userId
            )[0][Model::HAVING_CONVERSATION][0][Model::UID];
        } else {
            throw new NoOngoingConversationException();
        }

        $conversation = ConversationQueryFactory::getConversationFromDGraphWithUid(
            $conversationUid,
            $this->dGraphClient
        );
        return $conversation;
    }

    /**
     * @param ChatbotUser $user
     * @throws GuzzleException
     */
    public function unsetCurrentIntent(ChatbotUser $user): void
    {
        $this->dGraphClient->deleteRelationship(
            $user->getCurrentConversationUid(),
            $user->getCurrentIntentUid(),
            Model::CURRENT_INTENT
        );

        $this->dGraphClient->deleteRelationship(
            $user->getUid(),
            $user->getCurrentIntentUid(),
            Model::CURRENT_INTENT
        );
    }

    /**
     * @param ChatbotUser $user
     * @throws GuzzleException
     */
    public function unsetCurrentConversation(ChatbotUser $user): void
    {
        $this->dGraphClient->createRelationship(
            $user->getCurrentConversationUid(),
            $user->getUid(),
            Model::HAD_CONVERSATION
        );

        $this->dGraphClient->deleteRelationship(
            $user->getCurrentConversationUid(),
            $user->getUid(),
            Model::HAVING_CONVERSATION
        );
    }

    /**
     * @param $userId
     * @return DGraphQuery
     */
    private function getUserQuery($userId): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq(Model::ID, $userId)
            ->filterEq(Model::EI_TYPE, Model::CHATBOT_USER)
            ->setQueryGraph([
                Model::UID,
                'expand(_all_)' => [
                    Model::UID,
                    'expand(_all_)' => [
                        Model::UID,
                    ]
                ]
            ]);

        return $dGraphQuery;
    }

    /**
     * @param $userId
     * @return array
     */
    private function getOngoingConversationIdQuery($userId): array
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq('id', $userId)
            ->filterEq(Model::EI_TYPE, Model::CHATBOT_USER)
            ->setQueryGraph([
                Model::UID,
                MODEL::HAVING_CONVERSATION => [
                    Model::UID,
                    Model::ID
                ],
            ]);

        $response = $this->dGraphClient->query($dGraphQuery);
        return $response->getData();
    }

    /**
     * @param $intentUid
     * @return string
     * TODO - should this be in the user service or the conversation store?
     */
    public function getSceneForIntent($intentUid): string
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->uid($intentUid)
            ->setQueryGraph([
                Model::LISTENED_BY => [
                    Model::UID,
                    Model::BOT_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ],
                    Model::USER_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ]
                ],
                Model::LISTENED_BY_FROM_SCENES => [
                    Model::UID,
                    Model::BOT_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ],
                    Model::USER_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ]
                ]
            ]);

        $response = $this->dGraphClient->query($dGraphQuery);
        $data = $response->getData()[0];

        if (isset($data[Model::LISTENED_BY][0][Model::BOT_PARTICIPATES_IN])) {
            return $data[Model::LISTENED_BY][0][Model::BOT_PARTICIPATES_IN][0][Model::ID];
        }
        if (isset($data[Model::LISTENED_BY][0][Model::USER_PARTICIPATES_IN])) {
            return $data[Model::LISTENED_BY][0][Model::USER_PARTICIPATES_IN][0][Model::ID];
        }
        if (isset($data[Model::LISTENED_BY_FROM_SCENES][0][Model::BOT_PARTICIPATES_IN])) {
            return $data[Model::LISTENED_BY_FROM_SCENES][0][Model::BOT_PARTICIPATES_IN][0][Model::ID];
        }
        if (isset($data[Model::LISTENED_BY_FROM_SCENES][0][Model::USER_PARTICIPATES_IN])) {
            return $data[Model::LISTENED_BY_FROM_SCENES][0][Model::USER_PARTICIPATES_IN][0][Model::ID];
        }

        // TODO throw an exception here
    }

    public function getCurrentSpeaker($intentUid): ?string
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->uid($intentUid)
            ->setQueryGraph([
                Model::SAID_BY => [
                    Model::UID,
                    Model::BOT_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ],
                    Model::USER_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ]
                ],
                Model::SAID_FROM_SCENES => [
                    Model::UID,
                    Model::BOT_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ],
                    Model::USER_PARTICIPATES_IN => [
                        Model::UID,
                        Model::ID
                    ]
                ]
            ]);

        $response = $this->dGraphClient->query($dGraphQuery);
        $data = $response->getData()[0];

        if (isset($data[Model::SAID_BY][0][Model::BOT_PARTICIPATES_IN])) {
            return Model::BOT;
        }
        if (isset($data[Model::SAID_BY][0][Model::USER_PARTICIPATES_IN])) {
            return Model::USER;
        }
        if (isset($data[Model::SAID_FROM_SCENES][0][Model::BOT_PARTICIPATES_IN])) {
            return Model::BOT;
        }
        if (isset($data[Model::SAID_FROM_SCENES][0][Model::USER_PARTICIPATES_IN])) {
            return Model::USER;
        }

        // TODO throw an exception here as there is nothing to return
    }

    /**
     * Sets the value of an attribute on a chatbot user
     *
     * @param ChatbotUser $chatbotUser
     * @param $attributeName
     * @param $attributeValue
     */
    protected function setUserAttribute(ChatbotUser $chatbotUser, $attributeName, $attributeValue): void
    {
        if ($chatbotUser->hasAttribute($attributeName)) {
            $chatbotUser->setAttribute($attributeName, $attributeValue);
        } else {
            try {
                $attribute = $this->attributeResolver->getAttributeFor($attributeName, $attributeValue);
                $chatbotUser->addAttribute($attribute);
            } catch (AttributeIsNotSupported $e) {
                Log::warning(sprintf('Trying to set unsupported attribute %s to user', $attributeName));
            }
        }
    }
}
