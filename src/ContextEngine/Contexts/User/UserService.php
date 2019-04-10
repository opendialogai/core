<?php


namespace OpenDialogAi\ContextEngine\Contexts\User;


use ContextEngine\AttributeResolver\AttributeCouldNotBeResolvedException;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Exceptions\NoOngoingConversationException;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\ConversationQueryFactory;
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
        $user->setAttribute('user.timestamp', microtime(true));
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
                'expand(_all_)',
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

    private function getConversation($conversationUid): DGraphQuery
    {

    }
}
