<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore;

use Ds\Map;
use Ds\Pair;
use Ds\Set;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelCondition;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelScene;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\ExpectedAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\ModelFacets;
use OpenDialogAi\Core\Conversation\Participant;
use OpenDialogAi\Core\Conversation\VirtualIntent;

class EIModelToGraphConverter
{
    /**
     * @param EIModelConversation $conversation
     * @param bool $clone
     * @return mixed
     */
    public function convertConversation(EIModelConversation $conversation, $clone = false)
    {
        $cm = new ConversationManager(
            $conversation->getId(),
            $conversation->getConversationStatus(),
            $conversation->getConversationVersion()
        );

        $clone ? false : $cm->getConversation()->setUid($conversation->getUid());
        $cm->getConversation()->setConversationType($conversation->getEiType());

        if ($conversation->getInstanceOf()) {
            $convertedTemplate = $this->convertConversation($conversation->getInstanceOf());
            $cm->setInstanceOf($convertedTemplate);
        }

        if ($conversation->getUpdateOf()) {
            $convertedTemplate = $this->convertConversation($conversation->getUpdateOf());
            $cm->setUpdateOf($convertedTemplate);
        }

        // Add any conversation level conditions
        /* @var Set $conditions */
        $conditions = $conversation->getConditions();
        if (!is_null($conditions) && $conditions->count() > 0) {
            $this->createConversationConditions($conditions, $cm);
        }

        // First create all the scenes
        $this->createScenes($cm, $conversation);

        // Now populate the scenes with data.
        $this->createScene($cm, $conversation->getOpeningScenes()->first(), $clone);

        // Cycle through all the other scenes and set those up as well.
        /* @var Set $scenes */
        $scenes = $conversation->getScenes();

        if (!is_null($scenes) && $scenes->count() > 0) {
            foreach ($scenes as $scene) {
                $this->createScene($cm, $scene, $clone);
            }
        }

        $allScenes = $conversation->getOpeningScenes()->merge($conversation->getScenes());
        $followedByMap = new Map();

        /** @var EIModelScene $sceneModel */
        foreach ($allScenes as $sceneModel) {
            /** @var EIModelIntent $intentModel */
            foreach ($sceneModel->getIntents() as $intentModel) {
                if ($intentModel->getFollowedBy()) {
                    $followedByMap->put($intentModel->getIntentUid(), new Map([
                        Model::UID => $intentModel->getFollowedBy(),
                        ModelFacets::CREATED_AT => $intentModel->getFollowedByCreatedAt()
                    ]));
                }
            }
        }

        foreach ($followedByMap as $key => $value) {
            $cm->intentFollows($key, $value[Model::UID], $value[ModelFacets::CREATED_AT]);
        }

        return $cm->getConversation();
    }

    /**
     * @param EIModelCondition $conditionData
     * @param bool $clone
     * @return Condition
     */
    public function convertCondition(EIModelCondition $conditionData, bool $clone = false): Condition
    {
        if (!is_null($conditionData)) {
            $condition = new Condition(
                $conditionData->getOperation(),
                $conditionData->getAttributes(),
                $conditionData->getParameters(),
                $conditionData->getId()
            );

            if (!$clone) {
                $condition->setUid($conditionData->getUid());
            }

            return $condition;
        }

        return null;
    }

    /**
     * Creates an intent with the provided intent data
     *
     * @param bool $clone
     * @param EIModelIntent $intentData
     * @return Intent
     */
    public function convertIntent(EIModelIntent $intentData, bool $clone = false): Intent
    {
        $intent = new Intent($intentData->getIntentId());
        $clone ? false : $intent->setUid($intentData->getIntentUid());
        $intent->setAttribute(Model::COMPLETES, $intentData->getCompletes());
        $intent->setCompletesAttribute($intentData->getCompletes());
        $intent->setConfidence($intentData->getConfidence());
        $intent->setOrderAttribute($intentData->getOrder());
        $intent->setRepeating($intentData->getRepeating());

        /* @var Pair $action */
        $actionPair = $intentData->getAction();
        if (!is_null($actionPair)) {
            $action = new Action($actionPair->key);
            $clone ? false : $action->setUid($actionPair->value);
            $intent->addAction($action);
        }

        /* @var Pair $interpreter */
        $interpreterPair = $intentData->getInterpreter();
        if (!is_null($interpreterPair)) {
            $interpreter = new Interpreter($interpreterPair->key);
            $clone ? false : $interpreter->setUid($interpreterPair->value);
            $intent->addInterpreter($interpreter);
        }

        foreach ($intentData->getExpectedAttributes() as $expectedAttributeUid => $expectedAttributeId) {
            $expectedAttributeNode = new ExpectedAttribute($expectedAttributeId);
            $clone ? false : $expectedAttributeNode->setUid($expectedAttributeUid);
            $intent->addExpectedAttribute($expectedAttributeNode);
        }

        /** @var EIModelCondition $conditionModel */
        foreach ($intentData->getConditions() as $conditionModel) {
            $condition = $this->convertCondition($conditionModel, $clone);

            if (!is_null($condition)) {
                $intent->addCondition($condition);
            }
        }

        foreach ($intentData->getInputActionAttributes() as $inputActionAttributeUid => $inputActionAttributeId) {
            $inputActionAttributeNode = new ExpectedAttribute($inputActionAttributeId);
            $clone ? false : $inputActionAttributeNode->setUid($inputActionAttributeUid);
            $intent->addInputActionAttribute($inputActionAttributeNode);
        }

        foreach ($intentData->getOutputActionAttributes() as $outputActionAttributeUid => $outputActionAttributeId) {
            $outputActionAttributeNode = new ExpectedAttribute($outputActionAttributeId);
            $clone ? false : $outputActionAttributeNode->setUid($outputActionAttributeUid);
            $intent->addOutputActionAttribute($outputActionAttributeNode);
        }

        $virtualIntentModel = $intentData->getVirtualIntent();
        if ($virtualIntentModel) {
            $virtualIntent = new VirtualIntent($virtualIntentModel->getId());
            $clone ? false : $virtualIntent->setUid($virtualIntentModel->getUid());
            $intent->addVirtualIntent($virtualIntent);
        }

        return $intent;
    }

    /**
     * @param Set $conditions
     * @param ConversationManager $cm
     * @param bool $clone
     */
    public function createConversationConditions(Set $conditions, ConversationManager $cm, bool $clone = false): void
    {
        /* @var EIModelCondition $conditionData */
        foreach ($conditions as $conditionData) {
            /* @var Condition $condition */
            $condition = $this->convertCondition($conditionData, $clone);

            if (isset($condition)) {
                $cm->addConditionToConversation($condition);
            }
        }
    }

    /**
     * @param $sceneId
     * @param Set $conditions
     * @param ConversationManager $cm
     * @param bool $clone
     */
    public function createSceneConditions($sceneId, Set $conditions, ConversationManager $cm, bool $clone = false): void
    {
        /* @var EIModelCondition $conditionData */
        foreach ($conditions as $conditionData) {
            /* @var Condition $condition */
            $condition = $this->convertCondition($conditionData, $clone);

            if (isset($condition)) {
                $cm->addConditionToScene($sceneId, $condition);
            }
        }
    }

    /**
     * @param ConversationManager $cm
     * @param EIModelConversation $data
     */
    public function createScenes(ConversationManager $cm, EIModelConversation $data): void
    {
        /* @var Set $openingScenes */
        $openingScenes = $data->getOpeningScenes();

        if (!is_null($openingScenes)) {
            /* @var EIModelScene $openingScene */
            foreach ($openingScenes as $openingScene) {
                $cm->createScene($openingScene->getId(), true);
            }
        }

        /* @var Set $scenes */
        $scenes = $data->getScenes();

        if (!is_null($scenes)) {
            /* @var EIModelScene $openingScene */
            foreach ($scenes as $scene) {
                $cm->createScene($scene->getId(), false);
            }
        }
    }

    /**
     * @param ConversationManager $cm
     * @param EIModelScene $data
     * @param bool $clone
     */
    public function createScene(ConversationManager $cm, EIModelScene $data, bool $clone = false): void
    {
        $scene = $cm->getScene($data->getId());
        $clone ? false : $scene->setUid($data->getUid());
        $clone ? false : $scene->getUser()->setUid($data->getUserUid());
        $clone ? false : $scene->getBot()->setUid($data->getBotUid());

        if ($data->hasConditions()) {
            $this->createSceneConditions($data->getId(), $data->getConditions(), $cm, $clone);
        }

        $this->updateParticipant(
            $scene->getId(), $scene->getUser(), $cm, $data->getUserSaysIntents(), $clone
        );

        $this->updateParticipant(
            $scene->getId(), $scene->getUser(), $cm, $data->getUserSaysAcrossScenesIntents(), $clone, true
        );

        $this->updateParticipant(
            $scene->getId(), $scene->getBot(), $cm, $data->getBotSaysIntents(), $clone
        );

        $this->updateParticipant(
            $scene->getId(), $scene->getBot(), $cm, $data->getBotSaysAcrossScenesIntents(), $clone, true
        );
    }

    /**
     * @param $sceneId
     * @param Participant $participant
     * @param ConversationManager $cm
     * @param Set $intents
     * @param bool $clone
     * @param bool $isSaidAcrossScenes
     */
    public function updateParticipant(
        $sceneId,
        Participant $participant,
        ConversationManager $cm,
        Set $intents,
        bool $clone = false,
        bool $isSaidAcrossScenes = false
    ): void {
        /* @var EIModelIntent $intentData */
        foreach ($intents as $intentData) {
            /* @var Intent $intent */
            $intent = $this->convertIntent($intentData, $clone);

            if ($isSaidAcrossScenes) {
                if ($participant->isUser()) {
                    $cm->userSaysToBotAcrossScenes($sceneId, $intentData->getNextScene(), $intent, $intentData->getOrder());
                }

                if ($participant->isBot()) {
                    $cm->botSaysToUserAcrossScenes($sceneId, $intentData->getNextScene(), $intent, $intentData->getOrder());
                }
            } else {
                if ($participant->isUser()) {
                    $cm->userSaysToBot($sceneId, $intent, $intentData->getOrder());
                }

                if ($participant->isBot()) {
                    $cm->botSaysToUser($sceneId, $intent, $intentData->getOrder());
                }
            }
        }
    }
}
