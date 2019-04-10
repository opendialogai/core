<?php


namespace OpenDialogAi\Core\Conversation;


use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

/**
 * Helper methods for forming queries to extract information from Dgraph.
 */
class ConversationQueryFactory
{
    /**
     * This queries Dgraph and retrieves a rebuilt Conversation Object.
     * @param string $conversationUid
     * @param DGraphClient $client
     * @return Conversation
     */
    public static function getConversationFromDgraph(string $conversationUid, DGraphClient $client)
    {
        $dGraphQuery = new DGraphQuery();

        $dGraphQuery->uid($conversationUid)
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::HAS_OPENING_SCENE => self::getSceneGraph(),
                Model::HAS_SCENE => self::getSceneGraph()
            ]);

        $response = $client->query($dGraphQuery)->getData()[0];
        return self::buildConversationFromDgraphData($response);
    }

    /**
     * @return array
     */
    private static function getSceneGraph() {
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
    private static function getParticipantGraph() {
        return [
            Model::UID,
            Model::ID,
            Model::SAYS => self::getIntentGraph(),
            Model::LISTENS_FOR => self::getIntentGraph(),
        ];
    }

    /**
     * @return array
     */
    private static function getIntentGraph() {
        return [
            Model::UID,
            Model::ID,
            Model::ORDER,
            Model::COMPLETES,
            Model::CAUSES_ACTION => self::getActionGraph(),
            Model::HAS_INTERPRETER => self::getInterpreterGraph()
        ];
    }

    /**
     * @return array
     */
    private static function getActionGraph() {
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
     * @return Conversation
     */
    private static function buildConversationFromDgraphData(array $data)
    {
        $cm = new ConversationManager($data[Model::ID]);
        $cm->getConversation()->setUid($data[Model::UID]);

        // Create the opening scene
        self::createSceneFromDgraphData($cm, $data[Model::HAS_OPENING_SCENE][0], true);

        // Cycle through all the other scenes and set those up as well.
        foreach ($data[Model::HAS_SCENE] as $scene) {
            self::createSceneFromDgraphData($cm, $scene);
        }

        return $cm->getConversation();
    }

    /**
     * @param ConversationManager $cm
     * @param $data
     * @param bool $opening
     */
    private static function createSceneFromDgraphData(ConversationManager $cm, $data, $opening = false)
    {
        $cm->createScene($data[Model::ID], $opening);
        $scene = $cm->getScene($data[Model::ID]);
        $scene->setUid($data[Model::UID]);
        $scene->getUser()->setUid(
            $data[Model::HAS_USER_PARTICIPANT][0][Model::UID]
        );
        self::updateParticipantFromDgraphData(
            $scene->getId(), $scene->getUser(), $cm, $data[Model::HAS_USER_PARTICIPANT][0]
        );
        $scene->getBot()->setUid(
            $data[Model::HAS_BOT_PARTICIPANT][0][Model::UID]
        );
        self::updateParticipantFromDgraphData(
            $scene->getId(), $scene->getBot(), $cm, $data[Model::HAS_BOT_PARTICIPANT][0]
        );
    }

    /**
     * @param $sceneId
     * @param Participant $participant
     * @param ConversationManager $cm
     * @param $data
     */
    private static function updateParticipantFromDgraphData(
        $sceneId,
        Participant $participant,
        ConversationManager $cm,
        $data
    )
    {
        foreach ($data[Model::SAYS] as $intentData) {
            $intent = new Intent($intentData[Model::ID]);
            $intent->setUid($intentData[Model::UID]);
            $intent->setAttribute(Model::COMPLETES, $intentData[MODEL::COMPLETES]);
            if (isset($intentData[Model::CAUSES_ACTION])) {
                $action = new Action($intentData[Model::CAUSES_ACTION][0][Model::ID]);
                $action->setUid($intentData[Model::CAUSES_ACTION][0][Model::UID]);
                $intent->addAction($action);
            }
            if (isset($intentData[Model::HAS_INTERPRETER])) {
                $interpreter = new Interpreter($intentData[Model::HAS_INTERPRETER][0][Model::ID]);
                $interpreter->setUid($intentData[Model::HAS_INTERPRETER][0][Model::UID]);
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
}
