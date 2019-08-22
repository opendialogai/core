<?php

namespace OpenDialogAi\ConversationEngine\Transformers;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;

/**
 * Transforms an Intent response from DGraph into an Intent Node object
 */
class IntentTransformer
{
    /**
     * @param array $data
     * @return Intent
     */
    static public function toIntent(array $data)
    {
        $node = new Intent($data[Model::ID], $data[Model::COMPLETES]);
        $node->setUid($data[Model::UID]);

        if (isset($data[Model::CONFIDENCE])) {
            $node->setConfidence($data[Model::CONFIDENCE]);
        }

        if (isset($data[Model::CAUSES_ACTION])) {
            $action = ActionTransformer::toAction($data[Model::CAUSES_ACTION][0]);
            $node->addAction($action);
        }

        if (isset($data[Model::ORDER])) {
            $node->setOrderAttribute($data[Model::ORDER]);
        }

        return $node;
    }
}
