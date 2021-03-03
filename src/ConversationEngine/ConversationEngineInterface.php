<?php

namespace OpenDialogAi\ConversationEngine;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Conversation\IntentCollection;

interface ConversationEngineInterface
{
    /**
     * Given an utterance attribute.
     * @param UtteranceAttribute $utterance
     * @return IntentCollection
     */
    public function getNextIntents(UtteranceAttribute $utterance): IntentCollection;
}
