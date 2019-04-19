<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Participant;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use PHPUnit\Framework\Constraint\Attribute;

/**
 * Helper methods for forming queries to extract information from Dgraph.
 */
class ConversationQueryFactory
{
    public static function getConversationTemplateIds(DGraphClient $client)
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
               Model::UID,
               MODEL::ID
            ]);

        $response = $client->query($dGraphQuery)->getData();
        return $response;
    }


    /**
     * @param string $conversationUid
     * @param DGraphClient $client
     * @param AttributeResolver $attributeResolver
     * @param bool $clone
     * @return Conversation
     */
    public static function getConversationFromDgraphWithUid(
        string $conversationUid,
        DGraphClient $client,
        AttributeResolver $attributeResolver,
        $clone = false
    ) {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->uid($conversationUid)
            ->setQueryGraph(self::getConversationQueryGraph());

        $response = $client->query($dGraphQuery)->getData()[0];
        return self::buildConversationFromDgraphData($response, $attributeResolver, $clone);
    }

    /**
     * @param string $templateName
     * @param DGraphClient $client
     * @param AttributeResolver $attributeResolver
     * @param bool $clone
     * @return Conversation
     */
    public static function getConversationFromDgraphWithTemplateName(
        string $templateName,
        DGraphClient $client,
        AttributeResolver $attributeResolver,
        $clone = false
    ) {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq('id', $templateName)
            ->setQueryGraph(self::getConversationQueryGraph());

        $response = $client->query($dGraphQuery)->getData()[0];
        return self::buildConversationFromDgraphData($response, $attributeResolver, $clone);
    }

    /**
     * @return array
     */
    public static function getConversationQueryGraph()
    {
        return [
            Model::UID,
            Model::ID,
            Model::EI_TYPE,
            Model::HAS_CONDITION => self::getConditionGraph(),
            Model::HAS_OPENING_SCENE => self::getSceneGraph(),
            Model::HAS_SCENE => self::getSceneGraph()
        ];
    }

    private static function getConditionGraph()
    {
        return [
            Model::UID,
            Model::ID,
            Model::ATTRIBUTE_NAME,
            Model::ATTRIBUTE_VALUE,
            Model::CONTEXT,
            Model::OPERATION
        ];
    }

    /**
     * @return array
     */
    private static function getSceneGraph()
    {
        return [
            Model::UID,
            Model::ID,
            Model::HAS_USER_PARTICIPANT => self::getParticipantGraph(),
            Model::HAS_BOT_PARTICIPANT => self::getParticipantGraph()
        ];
    }

    /**
     * @return array
     */
    private static function getParticipantGraph()
    {
        return [
            Model::UID,
            Model::ID,
            Model::SAYS => self::getIntentGraph(),
            Model::SAYS_ACROSS_SCENES => self::getIntentGraph(),
            Model::LISTENS_FOR => self::getIntentGraph(),
            Model::LISTENS_FOR_ACROSS_SCENES => self::getIntentGraph(),
        ];
    }

    /**
     * @return array
     */
    private static function getIntentGraph()
    {
        return [
            Model::UID,
            Model::ID,
            Model::ORDER,
            Model::COMPLETES,
            Model::CONFIDENCE,
            Model::CAUSES_ACTION => self::getActionGraph(),
            Model::HAS_INTERPRETER => self::getInterpreterGraph(),
            Model::LISTENED_BY_FROM_SCENES => [
                Model::UID,
                Model::ID,
                Model::USER_PARTICIPATES_IN => [
                    Model::ID,
                ],
                Model::BOT_PARTICIPATES_IN => [
                    Model::ID,
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private static function getActionGraph()
    {
        return [
            Model::UID,
            Model::ID,
        ];
    }

    /**
     * @return array
     */
    private static function getInterpreterGraph()
    {
        return [
            Model::UID,
            Model::ID
        ];
    }


    /**
     * @param array $data
     * @param AttributeResolver $attributeResolver
     * @param bool $clone
     * @return mixed
     */
    private static function buildConversationFromDgraphData(array $data, AttributeResolver $attributeResolver, $clone = false)
    {
        $cm = new ConversationManager($data[Model::ID]);
        $clone ? false : $cm->getConversation()->setUid($data[Model::UID]);
        $cm->getConversation()->setConversationType($data[Model::EI_TYPE]);

        // Add any conversation level conditions
        if (isset($data[Model::HAS_CONDITION])) {
            self::createConversationConditions($data[Model::HAS_CONDITION], $cm, $attributeResolver);
        }

        // First create all the scenes
        self::createScenesFromDgraphData($cm, $data);

        // Now populate the scenes with data.
        self::createSceneFromDgraphData($cm, $data[Model::HAS_OPENING_SCENE][0], $clone);

        // Cycle through all the other scenes and set those up as well.
        if (isset($data[Model::HAS_SCENE])) {
            foreach ($data[Model::HAS_SCENE] as $scene) {
                self::createSceneFromDgraphData($cm, $scene, $clone);
            }
        }

        return $cm->getConversation();
    }

    /**
     * @param array $conditions
     * @param ConversationManager $cm
     * @param AttributeResolver $attributeResolver
     * @param bool $clone
     */
    public static function createConversationConditions(
        array $conditions,
        ConversationManager $cm,
        AttributeResolver $attributeResolver,
        bool $clone = false
    ) {
        foreach ($conditions as $condition_attributes) {
            $uid = $condition_attributes[Model::UID];
            $id = $condition_attributes[Model::ID];
            $context = $condition_attributes[Model::CONTEXT];
            $attributeName = $condition_attributes[Model::ATTRIBUTE_NAME];
            $attributeValue = $condition_attributes[Model::ATTRIBUTE_VALUE] === ''
                ? null
                : $condition_attributes[Model::ATTRIBUTE_VALUE];
            $operation = $condition_attributes[Model::OPERATION];

            if (array_key_exists($attributeName, $attributeResolver->getSupportedAttributes())) {
                $attribute = $attributeResolver->getAttributeFor($attributeName, $attributeValue);
                $condition = new Condition($attribute, $operation, $id);
                $condition->setContextId($context);
                if ($clone) {
                    $condition->setUid($uid);
                }
                $cm->addConditionToConversation($condition);
            }
        }
    }

    /**
     * @param ConversationManager $cm
     * @param $data
     */
    private static function createScenesFromDgraphData(ConversationManager $cm, $data)
    {
        foreach ($data[Model::HAS_OPENING_SCENE] as $openingScene) {
            $cm->createScene($openingScene[Model::ID], true);
        }

        if (isset($data[Model::HAS_SCENE])) {
            foreach ($data[Model::HAS_SCENE] as $scene) {
                $cm->createScene($scene[Model::ID], false);
            }
        }
    }

    /**
     * @param ConversationManager $cm
     * @param $data
     * @param bool $opening
     * @param bool $clone
     */
    private static function createSceneFromDgraphData(ConversationManager $cm, $data, bool $clone = false)
    {
        $scene = $cm->getScene($data[Model::ID]);
        $clone ? false : $scene->setUid($data[Model::UID]);
        $clone ? false: $scene->getUser()->setUid($data[Model::HAS_USER_PARTICIPANT][0][Model::UID]);
        $clone ? false: $scene->getBot()->setUid($data[Model::HAS_BOT_PARTICIPANT][0][Model::UID]);

        self::updateParticipantFromDgraphData(
            $scene->getId(), $scene->getUser(), $cm, $data[Model::HAS_USER_PARTICIPANT][0], $clone
        );

        self::updateParticipantFromDgraphData(
            $scene->getId(), $scene->getBot(), $cm, $data[Model::HAS_BOT_PARTICIPANT][0], $clone
        );
    }

    /**
     * @param $sceneId
     * @param Participant $participant
     * @param ConversationManager $cm
     * @param $data
     * @param bool $clone
     */
    private static function updateParticipantFromDgraphData(
        $sceneId,
        Participant $participant,
        ConversationManager $cm,
        $data,
        bool $clone = false
    ) {
        if (isset($data[Model::SAYS])) {
            foreach ($data[Model::SAYS] as $intentData) {
                $intent = new Intent($intentData[Model::ID]);
                $clone ? false : $intent->setUid($intentData[Model::UID]);
                $intent->setAttribute(Model::COMPLETES, $intentData[MODEL::COMPLETES]);

                if (isset($intentData[Model::CONFIDENCE])) {
                    $intent->setConfidence($intentData[Model::CONFIDENCE]);
                }

                if (isset($intentData[Model::CAUSES_ACTION])) {
                    $action = new Action($intentData[Model::CAUSES_ACTION][0][Model::ID]);
                    $clone ? false : $action->setUid($intentData[Model::CAUSES_ACTION][0][Model::UID]);
                    $intent->addAction($action);
                }
                if (isset($intentData[Model::HAS_INTERPRETER])) {
                    $interpreter = new Interpreter($intentData[Model::HAS_INTERPRETER][0][Model::ID]);
                    $clone ? false : $interpreter->setUid($intentData[Model::HAS_INTERPRETER][0][Model::UID]);
                    $intent->addInterpreter($interpreter);
                }

                if ($participant->isUser()) {
                    $cm->userSaysToBot($sceneId, $intent, $intentData[Model::ORDER]);
                }

                if ($participant->isBot()) {
                    $cm->botSaysToUser($sceneId, $intent, $intentData[Model::ORDER]);
                }
            }
        }
        if (isset($data[Model::SAYS_ACROSS_SCENES])) {
            foreach ($data[Model::SAYS_ACROSS_SCENES] as $intentData) {
                $intent = new Intent($intentData[Model::ID]);
                $clone ? false : $intent->setUid($intentData[Model::UID]);
                $intent->setAttribute(Model::COMPLETES, $intentData[MODEL::COMPLETES]);

                if (isset($intentData[Model::CONFIDENCE])) {
                    $intent->setConfidence($intentData[Model::CONFIDENCE]);
                }

                if (isset($intentData[Model::CAUSES_ACTION])) {
                    $action = new Action($intentData[Model::CAUSES_ACTION][0][Model::ID]);
                    $clone ? false : $action->setUid($intentData[Model::CAUSES_ACTION][0][Model::UID]);
                    $intent->addAction($action);
                }
                if (isset($intentData[Model::HAS_INTERPRETER])) {
                    $interpreter = new Interpreter($intentData[Model::HAS_INTERPRETER][0][Model::ID]);
                    $clone ? false : $interpreter->setUid($intentData[Model::HAS_INTERPRETER][0][Model::UID]);
                    $intent->addInterpreter($interpreter);
                }

                $endingSceneId = $intentData[Model::LISTENED_BY_FROM_SCENES][0][Model::USER_PARTICIPATES_IN][0][Model::ID];

                if ($participant->isUser()) {
                    $cm->userSaysToBotAcrossScenes($sceneId, $endingSceneId, $intent, $intentData[Model::ORDER]);
                }

                if ($participant->isBot()) {
                    $cm->botSaysToUserAcrossScenes($sceneId, $endingSceneId, $intent, $intentData[Model::ORDER]);
                }
            }
        }
    }
}
