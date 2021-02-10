<?php


use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Attributes\TimestampAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FormDataAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserHistoryRecord;
use OpenDialogAi\AttributeEngine\Attributes\ArrayDataAttribute;

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

        'current_conversation' => StringAttribute::class,
        'current_scene' => StringAttribute::class,
        'current_intent' => StringAttribute::class,
        'interpreted_intent' => StringAttribute::class,

        // Utterances
        'utterance' => UtteranceAttribute::class,
        'utterance_platform' => StringAttribute::class,
        'utterance_type' => StringAttribute::class,
        'utterance_data' => FormDataAttribute::class,
        'utterance_value' => StringAttribute::class,
        'utterance_form_data' => FormDataAttribute::class,
        'utterance_text' => StringAttribute::class,
        'callback_id'=> StringAttribute::class,
        'utterance_user_id' => StringAttribute::class,
        'utterance_user' => UserAttribute::class,

        // User
        'current_user' => UserAttribute::class,
        'custom_parameters' => FormDataAttribute::class,
        'last_record' => UserHistoryRecord::class,

        // Conversation State
        'platform' => StringAttribute::class,
        'workspace' => StringAttribute::class,
        'speaker' => StringAttribute::class,
        'intent_id' => StringAttribute::class,
        'scenario_id' => StringAttribute::class,
        'conversation_id' => StringAttribute::class,
        'turn_id' => StringAttribute::class,
        'actions_performed' => ArrayDataAttribute::class,
        'conditions_evaluated' => ArrayDataAttribute::class,
    ],
];
