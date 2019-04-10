<?php


namespace OpenDialogAi\ContextEngine\Contexts\User;


use ContextEngine\AttributeResolver\AttributeCouldNotBeResolvedException;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Exceptions\NoOngoingConversationException;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationQueryFactory;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\Node\Node;
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

    public function setAttributeResolver(AttributeResolver $attributeResolver)
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * This retrieves the user from dgraph and sets the current conversation
     *
     * @param $userId
     * @return Node
     */
    public function getUser($userId): ChatbotUser
    {
        $response = $this->dGraphClient->query($this->getUserQuery($userId));

        $user = new ChatbotUser();
        if (isset($response->getData()[0]['id'])) {
            foreach ($response->getData()[0] as $name => $value) {
                if ($name == 'id') {
                    $user->setId($value);
                    continue;
                }

                if ($name == 'uid') {
                    $user->setUid($value);
                    continue;
                }

                try {
                    $attribute = $this->attributeResolver->getAttributeFor($name, $value);
                    $user->addAttribute($attribute);
                } catch (AttributeCouldNotBeResolvedException $e) {
                    // Simply skip attributes we can't deal with.
                    continue;
                }
            }
        }

        if (isset($response->getData()[0][Model::HAVING_CONVERSATION])) {
            $conversation = ConversationQueryFactory::getConversationFromDgraph(
                $response->getData()[0][Model::HAVING_CONVERSATION][0][Model::UID],
                $this->dGraphClient
            );

            $user->setCurrentConversation($conversation);
        }

        if (isset($response->getData()[0][Model::HAVING_CONVERSATION][0][Model::CURRENT_INTENT])) {
            $intent = $user->getIntentByUid(
                $response->getData()[0][Model::HAVING_CONVERSATION][0][Model::CURRENT_INTENT][0][Model::UID]
            );
            $user->setCurrentIntent($intent);
        }
        return $user;
    }

    /**
     * @param UtteranceInterface $utterance
     * @return ChatbotUser
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    public function createOrUpdateUser(UtteranceInterface $utterance): ChatbotUser
    {
        if ($this->userExists($utterance->getUserId())) {
            return $this->updateUserFromUtterance($utterance);
        } else {
            return $this->createUserFromUtterance($utterance);
        }
    }

    /**
     * @param UtteranceInterface $utterance
     * @return bool|Node
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    public function updateUserFromUtterance(UtteranceInterface $utterance)
    {
        // We are dealing with an existing user to go get them
        $user = $this->getUser($utterance->getUserId());

        // @todo identify what needs to be updated - this is just a dummy action now
        $user->setAttribute('timestamp', microtime(true));
        return $this->updateUser($user);
    }

    /**
     * @param UtteranceInterface $utterance
     * @return Node | bool
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    public function createUserFromUtterance(UtteranceInterface $utterance)
    {
        $user = new ChatbotUser($utterance->getUserId());
        $user->addAttribute(new IntAttribute('timestamp', microtime(true)));

        return $this->updateUser($user);
    }

    /**
     * @param ChatbotUser $user
     * @param Conversation $conversation
     */
    public function setCurrentConversation(ChatbotUser $user, Conversation $conversation)
    {
        $user->setCurrentConversation($conversation);
        $this->updateUser($user);
    }

    /**
     *
     */
    public function setCurrentIntent(ChatbotUser $user, Intent $intent)
    {
        $user->setCurrentIntent($intent);
        $this->updateUser($user);
    }

    /**
     * @param Node $user
     * @return Node
     */
    public function updateUser(ChatbotUser $user)
    {
        $mutation = new DGraphMutation($user);
        $mutationResponse = $this->dGraphClient->tripleMutation($mutation);

        if ($mutationResponse->isSuccessful()) {
            return $this->getUser($user->getId());
        }

        throw new CouldNotRetrieveUserRecordException();
    }

    /**
     * @param $userId
     * @return bool
     */
    public function userExists($userId)
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
     */
    public function getCurrentConversation($userId)
    {
        if ($this->userIsHavingConversation($userId)) {
            $conversationId = $this->getOngoingConversationIdQuery(
                $userId
            )[0][Model::HAVING_CONVERSATION][0][Model::UID];
        } else {
            throw new NoOngoingConversationException();
        }

        $conversation = ConversationQueryFactory::getConversationFromDgraph($conversationId, $this->dGraphClient);
        return $conversation;
    }

    /**
     * @param ChatbotUser $user
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unsetCurrentIntent(ChatbotUser $user)
    {
        $this->dGraphClient->deleteRelationship(
            $user->getCurrentConversation()->getUid(),
            $user->getCurrentIntent()->getUid(),
            Model::CURRENT_INTENT
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
     */
    public function getSceneForIntent($intentUid): string
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
                ]
            ]);

        $response = $this->dGraphClient->query($dGraphQuery);
        $data = $response->getData()[0];

        if (isset($data[Model::SAID_BY][0][Model::BOT_PARTICIPATES_IN])) {
            return ($data[Model::SAID_BY][0][Model::BOT_PARTICIPATES_IN][0][Model::ID]);
        }
        if (isset($data[Model::SAID_BY][0][Model::USER_PARTICIPATES_IN])) {
            return ($data[Model::SAID_BY][0][Model::USER_PARTICIPATES_IN][0][Model::ID]);
        }
    }

}
