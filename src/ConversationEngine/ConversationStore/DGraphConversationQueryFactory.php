<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore;

use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

/**
 * Helper methods for forming queries to extract information from DGraph.
 */
class DGraphConversationQueryFactory implements ConversationQueryFactoryInterface
{
    public static function getAllOpeningIntents(): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->filterEq(Model::CONVERSATION_STATUS, Conversation::ACTIVATED)
            ->setQueryGraph([
                Model::EI_TYPE,
                Model::ID,
                Model::UID,
                Model::HAS_CONDITION => self::getConditionGraph(),
                Model::HAS_OPENING_SCENE => [
                    Model::HAS_USER_PARTICIPANT => [
                        Model::SAYS => [
                            Model::ID,
                            Model::UID,
                            Model::ORDER,
                            Model::CONFIDENCE,
                            Model::CAUSES_ACTION => [
                                Model::UID,
                                Model::ID
                            ],
                            Model::HAS_INTERPRETER => [
                                Model::ID,
                                Model::UID,
                            ],
                            Model::HAS_EXPECTED_ATTRIBUTE => [
                                Model::ID,
                                Model::UID
                            ]
                        ],
                        Model::SAYS_ACROSS_SCENES => [
                            Model::ID,
                            Model::UID,
                            Model::ORDER,
                            Model::CONFIDENCE,
                            Model::CAUSES_ACTION => [
                                Model::UID,
                                Model::ID
                            ],
                            Model::HAS_INTERPRETER => [
                                Model::ID,
                                Model::UID,
                            ],
                            Model::HAS_EXPECTED_ATTRIBUTE => [
                                Model::ID,
                                Model::UID
                            ]
                        ]
                    ],
                ]
            ]);
        return $dGraphQuery;
    }

    /**
     * @return DGraphQuery
     */
    public static function getConversationTemplateIds(): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
               Model::UID,
               MODEL::ID
            ]);
        return $dGraphQuery;
    }

    /**
     * @param string $conversationUid
     * @return DGraphQuery
     */
    public static function getConversationFromDGraphWithUid(string $conversationUid): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->uid($conversationUid)->setQueryGraph(self::getConversationQueryGraph());
        return $dGraphQuery;
    }

    /**
     * @param string $conversationUid
     * @return DGraphQuery
     */
    public static function getConversationTemplateFromDGraphWithUid(string $conversationUid): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->uid($conversationUid)->setQueryGraph(self::getConversationTemplateQueryGraph());
        return $dGraphQuery;
    }

    /**
     * @param string $templateName
     * @return DGraphQuery
     */
    public static function getConversationFromDGraphWithTemplateName(string $templateName): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->eq(Model::ID, $templateName)
            ->filterEq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph(self::getConversationTemplateQueryGraph());
        return $dGraphQuery;
    }

    /**
     * @param string $templateName
     * @return DGraphQuery
     */
    public static function getConversationTemplateUid(string $templateName): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->eq(Model::ID, $templateName)
            ->filterEq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
                Model::UID,
                Model::ID
            ]);
        return $dGraphQuery;
    }

    /**
     * Gets a user conversation by uid
     *
     * @param string $conversationId
     * @return DGraphQuery
     */
    public static function getUserConversation(string $conversationId): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->uid($conversationId)
            ->filterEq(Model::EI_TYPE, Model::CONVERSATION_USER)
            ->setQueryGraph(self::getConversationQueryGraph());

        return $dGraphQuery;
    }

    /**
     * Gets an intent by uid
     *
     * @param string $intentUid
     * @return DGraphQuery
     */
    public static function getIntentByUid(string $intentUid): DGraphQuery
    {
        $dGraphQuery = new DGraphQuery();
        $dGraphQuery->uid($intentUid)
            ->filterEq(Model::EI_TYPE, Model::INTENT)
            ->setQueryGraph(self::getIntentGraph());
        return $dGraphQuery;
    }

    /**
     * @return array
     */
    public static function getConversationQueryGraph(): array
    {
        return array_merge(
            self::getConversationTemplateQueryGraph(),
            [
                Model::INSTANCE_OF => self::getConversationTemplateQueryGraph()
            ]
        );
    }

    /**
     * @return array
     */
    public static function getConversationTemplateQueryGraph(): array
    {
        return [
            Model::UID,
            Model::ID,
            Model::EI_TYPE,
            Model::CONVERSATION_STATUS,
            Model::CONVERSATION_VERSION,
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
            Model::EI_TYPE,
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
}
