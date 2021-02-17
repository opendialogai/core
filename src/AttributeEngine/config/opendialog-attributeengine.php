<?php


use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Attributes\TimestampAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FormDataAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserHistoryRecord;
use OpenDialogAi\AttributeEngine\Attributes\ArrayDataAttribute;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\Intent;


return [
    'supported_attribute_types' => [
        BooleanAttribute::class,
        FloatAttribute::class,
        IntAttribute::class,
        StringAttribute::class,
        TimestampAttribute::class,
        UserAttribute::class,
        UtteranceAttribute::class,
        FormDataAttribute::class,
        ArrayDataAttribute::class,
        UserHistoryRecord::class,
        BasicCompositeAttribute::class,
    ],

    'supported_attributes' => [
        'callback_value' => StringAttribute::class,
        'context' => StringAttribute::class,
        'email' => StringAttribute::class,
        'external_id' => StringAttribute::class,
        'first_name' => StringAttribute::class,
        'full_name' => StringAttribute::class,
        'id' => StringAttribute::class,
        'last_name' => StringAttribute::class,
        'age' => IntAttribute::class,
        'name' => StringAttribute::class,
        'operation' => StringAttribute::class,
        'timestamp' => IntAttribute::class,
        'last_seen' => TimestampAttribute::class,
        'first_seen' => TimestampAttribute::class,
        'all' => StringAttribute::class,
        'composite' => BasicCompositeAttribute::class,

        'qna_answer' => StringAttribute::class,
        'qna_prompt_0' => StringAttribute::class,
        'qna_prompt_1' => StringAttribute::class,
        'qna_prompt_2' => StringAttribute::class,
        'qna_prompt_3' => StringAttribute::class,
        'qna_prompt_4' => StringAttribute::class,

        // Conversation State & Records
        Conversation::CURRENT_CONVERSATION => StringAttribute::class,
        Scene::CURRENT_SCENE => StringAttribute::class,
        Intent::CURRENT_INTENT => StringAttribute::class,
        Turn::CURRENT_TURN => StringAttribute::class,
        Intent::INTERPRETED_INTENT => StringAttribute::class,
        Intent::CURRENT_SPEAKER => StringAttribute::class,
        'platform' => StringAttribute::class,
        'workspace' => StringAttribute::class,
        'intent_id' => StringAttribute::class,
        'scenario_id' => StringAttribute::class,
        'conversation_id' => StringAttribute::class,
        'turn_id' => StringAttribute::class,
        'completed' => BooleanAttribute::class,
        'actions_performed' => ArrayDataAttribute::class,
        'conditions_evaluated' => ArrayDataAttribute::class,

        // Utterances
        UtteranceAttribute::UTTERANCE => UtteranceAttribute::class,
        UtteranceAttribute::UTTERANCE_PLATFORM => StringAttribute::class,
        UtteranceAttribute::TYPE => StringAttribute::class,
        UtteranceAttribute::UTTERANCE_DATA => FormDataAttribute::class,
        UtteranceAttribute::UTTERANCE_DATA_VALUE => StringAttribute::class,
        UtteranceAttribute::UTTERANCE_FORM_DATA => FormDataAttribute::class,
        UtteranceAttribute::UTTERANCE_TEXT => StringAttribute::class,
        UtteranceAttribute::CALLBACK_ID=> StringAttribute::class,
        UtteranceAttribute::UTTERANCE_USER_ID => StringAttribute::class,
        UtteranceAttribute::UTTERANCE_USER => UserAttribute::class,

        // User
        'utterance_user' => UserAttribute::class,
        'current_user' => UserAttribute::class,
        'custom_parameters' => FormDataAttribute::class,
        'history_record' => UserHistoryRecord::class,

    ],
];
