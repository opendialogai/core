<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver as AttributeResolverFacade;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\ExpectedAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Participant;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;

/**
 * Helper methods for forming queries to extract information from DGraph.
 *
 * TODO this isn't a query factory as its actually running the queries.
 * TODO Move the running of the queries into the conversation store
 */
class ConversationQueryFactory
{
    /**
     * @param DGraphClient $client
     * @return mixed
     */
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
     * @param bool $clone
     * @return Conversation
     */
    public static function getConversationFromDGraphWithUid(
        string $conversationUid,
        DGraphClient $client,
        $clone = false
    ): Conversation {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->uid($conversationUid)->setQueryGraph(self::getConversationQueryGraph());

        $response = $client->query($dGraphQuery)->getData()[0];
        return self::buildConversationFromDGraphData($response, $clone);
    }

    /**
     * @param string $templateName
     * @param DGraphClient $client
     * @param bool $clone
     * @return Conversation
     */
    public static function getConversationFromDGraphWithTemplateName(
        string $templateName,
        DGraphClient $client,
        $clone = false
    ): Conversation {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq('id', $templateName)
            ->filterEq('ei_type', Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph(self::getConversationQueryGraph());

        $response = $client->query($dGraphQuery)->getData()[0];
        return self::buildConversationFromDGraphData($response, $clone);
    }

    /**
     * @param string $templateName
     * @param DGraphClient $client
     * @return string
     */
    public static function getConversationTemplateUid(string $templateName, DGraphClient $client): string
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->eq('id', $templateName)
            ->filterEq('ei_type', Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
                Model::UID,
                Model::ID
            ]);

        $response = $client->query($dGraphQuery)->getData()[0];
        return $response['uid'];
    }

    /**
     * @return array
     */
    public static function getConversationQueryGraph(): array
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

    /**
     * @return array
     */
    public static function getConditionGraph(): array
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
    public static function getSceneGraph(): array
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
    public static function getParticipantGraph(): array
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
    public static function getIntentGraph(): array
    {
        return [
            Model::UID,
            Model::ID,
            Model::ORDER,
            Model::COMPLETES,
            Model::CONFIDENCE,
            Model::CAUSES_ACTION => self::getActionGraph(),
            Model::HAS_INTERPRETER => self::getInterpreterGraph(),
            Model::HAS_EXPECTED_ATTRIBUTE => self::getExpectedAttributesGraph(),
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
    public static function getActionGraph(): array
    {
        return [
            Model::UID,
            Model::ID,
        ];
    }

    /**
     * @return array
     */
    public static function getInterpreterGraph(): array
    {
        return [
            Model::UID,
            Model::ID
        ];
    }

    /**
     * @return array
     */
    public static function getExpectedAttributesGraph(): array
    {
        return [
            Model::UID,
            Model::ID
        ];
    }


    /**
     * @param array $data
     * @param bool $clone
     * @return mixed
     */
    public static function buildConversationFromDGraphData(array $data, $clone = false)
    {
        $cm = new ConversationManager($data[Model::ID]);
        $clone ? false : $cm->getConversation()->setUid($data[Model::UID]);
        $cm->getConversation()->setConversationType($data[Model::EI_TYPE]);

        // Add any conversation level conditions
        if (isset($data[Model::HAS_CONDITION])) {
            self::createConversationConditions($data[Model::HAS_CONDITION], $cm);
        }

        // First create all the scenes
        self::createScenesFromDGraphData($cm, $data);

        // Now populate the scenes with data.
        self::createSceneFromDGraphData($cm, $data[Model::HAS_OPENING_SCENE][0], $clone);

        // Cycle through all the other scenes and set those up as well.
        if (isset($data[Model::HAS_SCENE])) {
            foreach ($data[Model::HAS_SCENE] as $scene) {
                self::createSceneFromDGraphData($cm, $scene, $clone);
            }
        }

        return $cm->getConversation();
    }

    /**
     * @param array $conditions
     * @param ConversationManager $cm
     * @param bool $clone
     */
    public static function createConversationConditions(array $conditions, ConversationManager $cm, bool $clone = false): void
    {
        foreach ($conditions as $conditionData) {
            $condition = self::createCondition($conditionData, $clone);
            if (isset($condition)) {
                $cm->addConditionToConversation($condition);
            }
        }
    }

    /**
     * @param array $conditionData
     * @param bool $clone
     * @return Condition
     */
    public static function createCondition(array $conditionData, bool $clone = false): Condition
    {
        $uid = $conditionData[Model::UID];
        $id = $conditionData[Model::ID];
        $context = $conditionData[Model::CONTEXT];
        $attributeName = $conditionData[Model::ATTRIBUTE_NAME];
        $attributeValue = $conditionData[Model::ATTRIBUTE_VALUE] === ''
            ? null
            : $conditionData[Model::ATTRIBUTE_VALUE];
        $operation = $conditionData[Model::OPERATION];

        if (array_key_exists($attributeName, AttributeResolverFacade::getSupportedAttributes())) {
            $attribute = AttributeResolverFacade::getAttributeFor($attributeName, $attributeValue);
            $condition = new Condition($attribute, $operation, $id);
            $condition->setContextId($context);
            if ($clone) {
                $condition->setUid($uid);
            }
            return $condition;
        }

        return null;
    }

    /**
     * @param ConversationManager $cm
     * @param $data
     */
    public static function createScenesFromDGraphData(ConversationManager $cm, $data): void
    {
        foreach ($data[Model::HAS_OPENING_SCENE] as $openingScene) {
            if (isset($openingScene[Model::ID])) {
                $cm->createScene($openingScene[Model::ID], true);
            } else {
                Log::error('Trying to create opening scene with no id', $openingScene);
            }
        }

        if (isset($data[Model::HAS_SCENE])) {
            foreach ($data[Model::HAS_SCENE] as $scene) {
                if (isset($scene[Model::ID])) {
                    $cm->createScene($scene[Model::ID], false);
                } else {
                    Log::error('Trying to create scene with no id', $scene);
                }
            }
        }
    }

    /**
     * @param ConversationManager $cm
     * @param $data
     * @param bool $clone
     */
    public static function createSceneFromDGraphData(ConversationManager $cm, $data, bool $clone = false): void
    {
        $scene = $cm->getScene($data[Model::ID]);
        $clone ? false : $scene->setUid($data[Model::UID]);
        $clone ? false: $scene->getUser()->setUid($data[Model::HAS_USER_PARTICIPANT][0][Model::UID]);
        $clone ? false: $scene->getBot()->setUid($data[Model::HAS_BOT_PARTICIPANT][0][Model::UID]);

        self::updateParticipantFromDGraphData(
            $scene->getId(), $scene->getUser(), $cm, $data[Model::HAS_USER_PARTICIPANT][0], $clone
        );

        self::updateParticipantFromDGraphData(
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
    public static function updateParticipantFromDGraphData(
        $sceneId,
        Participant $participant,
        ConversationManager $cm,
        $data,
        bool $clone = false
    ): void {
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

                if (isset($intentData[Model::HAS_EXPECTED_ATTRIBUTE])) {
                    foreach ($intentData[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                        $expectedAttributeNode = new ExpectedAttribute($expectedAttribute[Model::ID]);
                        $clone ? false : $expectedAttributeNode->setUid($expectedAttribute[Model::UID]);

                        $intent->addExpectedAttribute($expectedAttributeNode);
                    }
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

                if (isset($intentData[Model::HAS_EXPECTED_ATTRIBUTE])) {
                    foreach ($intentData[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                        $expectedAttributeNode = new ExpectedAttribute($expectedAttribute[Model::ID]);
                        $clone ? false : $expectedAttributeNode->setUid($expectedAttribute[Model::UID]);

                        $intent->addExpectedAttribute($expectedAttributeNode);
                    }
                }

                $endingSceneId = self::getEndingSceneId($intentData);

                if ($participant->isUser()) {
                    $cm->userSaysToBotAcrossScenes($sceneId, $endingSceneId, $intent, $intentData[Model::ORDER]);
                }

                if ($participant->isBot()) {
                    $cm->botSaysToUserAcrossScenes($sceneId, $endingSceneId, $intent, $intentData[Model::ORDER]);
                }
            }
        }
    }

    /**
     * Tries to work out the ending scene ID from the intent data
     *
     * @param $intentData
     * @return mixed
     * @throws NodeDoesNotExistException
     */
    public static function getEndingSceneId($intentData)
    {
        $listenedBy = $intentData[Model::LISTENED_BY_FROM_SCENES][0];

        if (isset($listenedBy[Model::USER_PARTICIPATES_IN][0][Model::ID])) {
            return $listenedBy[Model::USER_PARTICIPATES_IN][0][Model::ID];
        }

        if (isset($listenedBy[Model::BOT_PARTICIPATES_IN][0][Model::ID])) {
            return $listenedBy[Model::BOT_PARTICIPATES_IN][0][Model::ID];
        }

        Log::error('Could not extract ending scene id', $listenedBy);
        throw new NodeDoesNotExistException('Could not extract ending scene id');
    }
}
