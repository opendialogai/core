<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class DGraphConversationStore implements ConversationStoreInterface
{
    private $dGraphClient;
    private $eiModelCreator;
    private $queryFactory;

    public function __construct(
        DGraphClient $dGraphClient,
        EIModelCreator $eiModelCreator,
        ConversationQueryFactoryInterface $queryFactory
    ) {
        $this->dGraphClient = $dGraphClient;
        $this->eiModelCreator = $eiModelCreator;
        $this->queryFactory = $queryFactory;
    }

    /**
     * @return EIModelOpeningIntents
     * @throws EIModelCreatorException
     */
    public function getAllOpeningIntents(): EIModelOpeningIntents
    {
        $query = $this->queryFactory::getAllOpeningIntents();
        $response = $this->dGraphClient->query($query);

        /* @var EIModelOpeningIntents $model */
        $model = $this->eiModelCreator->createEIModel(EIModelOpeningIntents::class, $response->getData());

        return $model;
    }

    /**
     * @param $conversationId
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getConversation($conversationId): EIModelConversation
    {
        $query = $this->queryFactory::getConversationFromDGraphWithUid($conversationId);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $model;
    }

    /**
     * @param $conversationTemplateName
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getConversationTemplate($conversationTemplateName): EIModelConversation
    {
        $query = DGraphConversationQueryFactory::getConversationFromDGraphWithTemplateName($conversationTemplateName);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $model;
    }

    /**
     * Gets the opening intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param int $order
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getOpeningIntentByConversationIdAndOrder($conversationId, int $order): EIModelIntent
    {
        $query = $this->queryFactory::getUserConversation($conversationId);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $conversationModel */
        $conversationModel = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $conversationModel->getIntentIdByOrder($order);
    }

    /**
     * @param $intentUid
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getIntentByUid($intentUid): EIModelIntent
    {
        $query = DGraphConversationQueryFactory::getIntentByUid($intentUid);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelIntent $model */
        $model = $this->eiModelCreator->createEIModel(EIModelIntent::class, $response->getData()[0]);

        return $model;
    }
}
