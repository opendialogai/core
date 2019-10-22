<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;

class DGraphConversationStore implements ConversationStoreInterface
{
    private $dGraphClient;
    private $eiModelCreator;
    private $queryFactory;

    /* @var EIModelToGraphConverter */
    private $conversationConverter;

    public function __construct(
        DGraphClient $dGraphClient,
        EIModelCreator $eiModelCreator,
        ConversationQueryFactoryInterface $queryFactory,
        EIModelToGraphConverter $conversationConverter
    ) {
        $this->dGraphClient = $dGraphClient;
        $this->eiModelCreator = $eiModelCreator;
        $this->queryFactory = $queryFactory;
        $this->conversationConverter = $conversationConverter;
    }

    /**
     * @return EIModelOpeningIntents
     * @throws EIModelCreatorException
     */
    public function getAllEIModelOpeningIntents(): EIModelOpeningIntents
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
    public function getEIModelConversation($conversationId): EIModelConversation
    {
        $query = $this->queryFactory::getConversationFromDGraphWithUid($conversationId);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $model;
    }

    /**
     * @param $conversationId
     * @param bool $clone
     * @return Conversation
     * @throws EIModelCreatorException
     */
    public function getConversation($conversationId, bool $clone = false): Conversation
    {
        $conversationModel = $this->getEIModelConversation($conversationId);
        return $this->conversationConverter->convertConversation($conversationModel, $clone);
    }

    /**
     * @param $conversationTemplateName
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getEIModelConversationTemplate($conversationTemplateName): EIModelConversation
    {
        $query = DGraphConversationQueryFactory::getConversationFromDGraphWithTemplateName($conversationTemplateName);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $model;
    }

    /**
     * @param $uid
     * @return Conversation
     * @throws EIModelCreatorException
     */
    public function getConversationTemplateByUid($uid): Conversation
    {
        $conversationModel = $this->getEIModelConversationTemplateByUid($uid);
        return $this->conversationConverter->convertConversation($conversationModel, false);
    }

    /**
     * @param $uid
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getEIModelConversationTemplateByUid($uid): EIModelConversation
    {
        $query = DGraphConversationQueryFactory::getConversationTemplateFromDGraphWithUid($uid);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $model;
    }

    /**
     * @param $templateName
     * @return Conversation
     * @throws EIModelCreatorException
     */
    public function getLatestTemplateVersionByName($templateName): Conversation
    {
        $conversationModel = $this->getLatestEIModelTemplateVersionByName($templateName);
        return $this->conversationConverter->convertConversation($conversationModel, false);
    }

    /**
     * @param $templateName
     * @return EIModelConversation
     * @throws EIModelCreatorException
     */
    public function getLatestEIModelTemplateVersionByName($templateName): EIModelConversation
    {
        $query = DGraphConversationQueryFactory::getConversationFromDGraphWithTemplateName($templateName);

        /** @var DGraphQueryResponse $response */
        $response = $this->dGraphClient->query($query);
        $data = $response->getData();

        // Sort by version number
        usort($data, function ($a, $b) {
            return $a[Model::CONVERSATION_VERSION] < $b[Model::CONVERSATION_VERSION];
        });

        /* @var EIModelConversation $model */
        $model = $this->eiModelCreator->createEIModel(EIModelConversation::class, $data[0]);

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
    public function getEIModelOpeningIntentByConversationIdAndOrder($conversationId, int $order): EIModelIntent
    {
        $query = $this->queryFactory::getUserConversation($conversationId);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelConversation $conversationModel */
        $conversationModel = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        return $conversationModel->getIntentIdByOrder($order);
    }

    /**
     * Gets the opening intent ID within a conversation with the given id with a matching order
     *
     * @param $conversationId
     * @param int $order
     * @return Intent
     * @throws EIModelCreatorException
     */
    public function getOpeningIntentByConversationIdAndOrder($conversationId, int $order): Intent
    {
        $currentIntentModel = $this->getEIModelOpeningIntentByConversationIdAndOrder(
            $conversationId,
            $order
        );
        return $this->conversationConverter->convertIntent($currentIntentModel);
    }

    /**
     * @param $intentUid
     * @return EIModelIntent
     * @throws EIModelCreatorException
     */
    public function getEIModelIntentByUid($intentUid): EIModelIntent
    {
        $query = DGraphConversationQueryFactory::getIntentByUid($intentUid);
        $response = $this->dGraphClient->query($query);

        /* @var EIModelIntent $model */
        $model = $this->eiModelCreator->createEIModel(EIModelIntent::class, $response->getData()[0]);

        return $model;
    }

    /**
     * @param $intentUid
     * @return Intent
     * @throws EIModelCreatorException
     */
    public function getIntentByUid($intentUid): Intent
    {
        $currentIntentModel = $this->getEIModelIntentByUid($intentUid);
        return $this->conversationConverter->convertIntent($currentIntentModel);
    }

    /**
     * @return EIModelToGraphConverter
     */
    public function getConversationConverter(): EIModelToGraphConverter
    {
        return $this->conversationConverter;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasConversationBeenUsed(string $name): bool
    {
        $query = DGraphConversationQueryFactory::hasConversationBeenUsed($name);
        $response = $this->dGraphClient->query($query);
        return !empty($response->getData());
    }
}
