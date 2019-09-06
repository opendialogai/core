<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;


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
use OpenDialogAi\Core\Conversation\Participant;

class EIModelConversationConverter
{
    /**
     * @param EIModelConversation $conversation
     * @param bool $clone
     * @return mixed
     */
    public static function buildConversationFromEIModel(EIModelConversation $conversation, $clone = false)
    {
        $cm = new ConversationManager($conversation->getId());
        $clone ? false : $cm->getConversation()->setUid($conversation->getUid());
        $cm->getConversation()->setConversationType($conversation->getEiType());

        // Add any conversation level conditions
        /* @var Set $conditions */
        $conditions = $conversation->getConditions();
        if (!is_null($conditions) && $conditions->count() > 0) {
            self::createConversationConditions($conditions, $cm);
        }

        // First create all the scenes
        self::createScenesFromEIModel($cm, $conversation);

        // Now populate the scenes with data.
        self::createSceneFromEIModel($cm, $conversation->getOpeningScenes()->first(), $clone);

        // Cycle through all the other scenes and set those up as well.
        /* @var Set $scenes */
        $scenes = $conversation->getScenes();

        if (!is_null($scenes) && $scenes->count() > 0) {
            foreach ($scenes as $scene) {
                self::createSceneFromEIModel($cm, $scene, $clone);
            }
        }

        return $cm->getConversation();
    }

    /**
     * @param Set $conditions
     * @param ConversationManager $cm
     * @param bool $clone
     */
    public static function createConversationConditions(Set $conditions, ConversationManager $cm, bool $clone = false): void
    {
        /* @var EIModelCondition $conditionData */
        foreach ($conditions as $conditionData) {
            /* @var Condition $condition */
            $condition = self::createCondition($conditionData, $clone);

            if (isset($condition)) {
                $cm->addConditionToConversation($condition);
            }
        }
    }

    /**
     * @param EIModelCondition $conditionData
     * @param bool $clone
     * @return Condition
     */
    public static function createCondition(EIModelCondition $conditionData, bool $clone = false): Condition
    {
        if (!is_null($conditionData)) {
            $condition = new Condition($conditionData->getAttribute(), $conditionData->getOperation(), $conditionData->getId());
            $condition->setContextId($conditionData->getContext());

            if ($clone) {
                $condition->setUid($conditionData->getUid());
            }

            return $condition;
        }

        return null;
    }

    /**
     * @param ConversationManager $cm
     * @param EIModelConversation $data
     */
    public static function createScenesFromEIModel(ConversationManager $cm, EIModelConversation $data): void
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
    public static function createSceneFromEIModel(ConversationManager $cm, EIModelScene $data, bool $clone = false): void
    {
        $scene = $cm->getScene($data->getId());
        $clone ? false : $scene->setUid($data->getUid());
        $clone ? false: $scene->getUser()->setUid($data->getUserUid());
        $clone ? false: $scene->getBot()->setUid($data->getBotUid());

        self::updateParticipantFromEIModel(
            $scene->getId(), $scene->getUser(), $cm, $data->getUserSaysIntents(), $clone
        );

        self::updateParticipantFromEIModel(
            $scene->getId(), $scene->getUser(), $cm, $data->getUserSaysAcrossScenesIntents(), $clone, true
        );

        self::updateParticipantFromEIModel(
            $scene->getId(), $scene->getBot(), $cm, $data->getBotSaysIntents(), $clone
        );

        self::updateParticipantFromEIModel(
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
    public static function updateParticipantFromEIModel(
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
            $intent = self::createIntent($intentData, $clone);

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

    /**
     * Creates an intent with the provided intent data
     *
     * @param bool $clone
     * @param EIModelIntent $intentData
     * @return Intent
     */
    public static function createIntent(EIModelIntent $intentData, bool $clone = false): Intent
    {
        $intent = new Intent($intentData->getIntentId());
        $clone ? false : $intent->setUid($intentData->getIntentUid());
        $intent->setAttribute(Model::COMPLETES, $intentData->getCompletes());
        $intent->setCompletesAttribute($intentData->getCompletes());
        $intent->setConfidence($intentData->getConfidence());
        $intent->setOrderAttribute($intentData->getOrder());

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

        return $intent;
    }
}
