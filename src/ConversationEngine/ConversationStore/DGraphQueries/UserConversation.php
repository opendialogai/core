<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;

use OpenDialogAi\ConversationEngine\Transformers\IntentTransformer;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;

/**
 * A model class to store the results of a user conversation type query
 */
class UserConversation
{
    private $uid;
    private $id;
    private $openingScene;
    private $scenes = [];

    public function __construct($data)
    {
        $this->uid = $data[Model::UID];
        $this->id = $data[Model::ID];
        $this->openingScene = isset($data[Model::HAS_OPENING_SCENE]) ? $data[Model::HAS_OPENING_SCENE][0] : null;

        if (isset($data[Model::HAS_SCENE])) {
            foreach ($data[Model::HAS_SCENE] as $scene) {
                $this->scenes[] = $scene;
            }
        }
    }

    /**
     * @param $order
     * @return Intent
     */
    public function getIntentIdByOrder($order): ?Intent
    {
        foreach ($this->getAllUserSaysIntents() as $intent) {
            if ($intent[Model::ORDER] == $order) {
                return IntentTransformer::toIntent($intent);
            }
        }

        return null;
    }

    /**
     * Gets all the intents said by user across the opening scene and all other scenes
     *
     * @return array
     */
    private function getAllUserSaysIntents()
    {
        $intents = $this->getUserIntentsFromScene($this->openingScene);

        foreach ($this->scenes as $scene) {
            $intents = array_merge($intents, $this->getUserIntentsFromScene($scene));
        }

        return $intents;
    }

    /**
     * @param $scene
     * @return array
     */
    private function getUserIntentsFromScene($scene): array
    {
        $intents = [];
        if (isset($scene[Model::HAS_USER_PARTICIPANT][0][Model::SAYS])) {
            foreach ($scene[Model::HAS_USER_PARTICIPANT][0][Model::SAYS] as $userSays) {
                $intents[] = $userSays;
            }
        }

        if (isset($scene[Model::HAS_USER_PARTICIPANT][0][Model::SAYS_ACROSS_SCENES])) {
            foreach ($scene[Model::HAS_USER_PARTICIPANT][0][Model::SAYS_ACROSS_SCENES] as $userSays) {
                $intents[] = $userSays;
            }
        }
        return $intents;
    }
}

