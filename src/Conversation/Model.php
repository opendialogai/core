<?php


namespace OpenDialogAi\Core\Conversation;

/**
 * Class Model
 * @package OpenDialog\Core\Conversation
 *
 * All the relationships and identifiers used in a conversation graph.
 */
class Model
{
    // Conversations and scenes have conditions.
    const HAS_CONDITION = 'has_condition';
    const HAS_OPENING_SCENE = 'has_opening_scene';
    const HAS_SCENE = 'has_scene';

    // Scenes have bot participants.
    const BOT = 'bot_participant';
    const USER = 'user_participant';
    const HAS_BOT_PARTICIPANT = 'has_bot_participant';
    const HAS_USER_PARTICIPANT = 'has_user_participant';

    // Participants can say (an intent) or listen for (an intent).
    const SAYS = 'says';
    const LISTENS_FOR = 'listens_for';

    // Intents can cause actions to be performed and can define interpreters.
    const PERFORM_ACTION = 'perform_action';
    const HAS_INTERPRETER = 'has_interpreter';

    const ORDER = 'core.attribute.order';
    const COMPLETES = 'core.attribute.completes';

    const TRANSITIONS_TO = 'transitions_to';
    const TRANSITIONS_FROM = 'transitions_from';
}
