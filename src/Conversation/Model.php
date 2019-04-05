<?php


namespace OpenDialogAi\Core\Conversation;

/**
 * All the relationships and identifiers used in a conversation graph.
 */
class Model
{
    // Attributes for conversation nodes
    const EI_TYPE = 'ei_type';
    const CONVERSATION = 'conversation';
    const CONDITION = 'condition';
    const SCENE = 'scene';
    const INTENT = 'intent';
    const PARTICIPANT = 'participant';
    const INTENT_INTERPRETER = 'intent_interpreter';
    const ACTION = 'action';
    const CHATBOT_USER = 'chatbot_user';

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
    const SAYS_ACROSS_SCENES = 'says_across_scenes';
    const LISTENS_FOR_ACROSS_SCENES = 'listens_for_across_scenes';

    // Intents can cause actions to be performed and can define interpreters.
    const CAUSES_ACTION = 'causes_action';
    const HAS_INTERPRETER = 'has_interpreter';

    const ORDER = 'core.attribute.order';
    const COMPLETES = 'core.attribute.completes';

    const TRANSITIONS_TO = 'transitions_to';
    const TRANSITIONS_FROM = 'transitions_from';
}
